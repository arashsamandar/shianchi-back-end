<?php

namespace App\Http\Controllers;

use App\Comment;
use App\CommentLike;
use App\Http\Requests\comments;
use App\Http\Requests\comments\GetCommentRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Permission;
use App\Repositories\CommentRepository;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Client;
use Tymon;
use Wego\Services\Captcha\CaptchaInterface;
use Wego\UserHandle\UserPermission;

class CommentController extends ApiController
{
    use Helpers;
    protected $commentRepository;
    const PAGINATE_SIZE = 5;
    protected $captcha;

    function __construct(CommentRepository $commentRepository, CaptchaInterface $captcha)
    {
        $this->commentRepository = $commentRepository;
        $this->captcha = $captcha;
    }

    /**
     * return comments ordered by rating
     *
     * need product's id as 'id' in request url
     *
     * @param GetCommentRequest $request
     * @return mixed
     */
    public function getCommentsByRating(GetCommentRequest $request)
    {
        $comments = $this->getConfirmedCommentById($request->input('product_id'), 'like');
        return $this->respondArray($comments);
    }

    /**
     * return comments ordered by time
     *
     *
     * need product's id as 'id' in request url
     *
     * @param GetCommentRequest $request
     * @return mixed
     */
    public function getCommentsByTime(GetCommentRequest $request)
    {
        $comments = $this->commentRepository->getConfirmedCommentsById($request->input('product_id'),'like');
        return $this->respondArray($comments);
    }

    public function incrementCommentLike(Request $request)
    {
        $user = UserPermission::checkBuyerPermission(); //check like comment permission
        $this->checkDuplicateLike($request->input('comment_id'));

        if (CommentLike::where('comment_id', '=', $request->input('id'))->count() === 0) {
            $d = Comment::where('id', '=', $request->input('id'))->increment('like');
            CommentLike::insert(['comment_id' => $request->input('id'), 'user_id' => $user->id]);

            return $this->respond('successfully liked');
        }
        return $this->respondNotFound("oh! you are liked before");
    }

    public function getConfirmedCommentById($productId, $sortWith = 'created_at', $sortOrder = 'desc')
    {
        return Comment::where('product_id', $productId)
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->select([
                'name', 'comments.id as comment_id',
                'comments.body', 'comments.created_at', 'comments.like'
            ])
            ->where('status', Comment::CONFIRMED)
            ->orderBy($sortWith, $sortOrder)
            ->paginate(self::PAGINATE_SIZE);
    }


    /**
     * @return mixed
     */
    public function getUserComments()
    {
        $user = $this->auth->user();
        $comments = Comment::where('user_id', '=', $user->id)
            ->join('products', 'products.id', '=', 'comments.product_id')
            ->select([
                'comments.id as comment_id', 'comments.status', 'comments.body',
                'comments.created_at', 'products.id as product_id',
                'products.persian_name', 'products.english_name'
            ])
            ->orderBy('comments.created_at', 'desc')
            ->paginate(self::PAGINATE_SIZE);
        return $this->respondArray($comments);
    }

    /**
     * @param StoreCommentRequest $request
     * @return mixed
     */
    public function store(StoreCommentRequest $request)
    {
        $user = $this->auth->user();
        Comment::insert([
            'user_id' => $user->id,
            'product_id' => $request->input('product_id'),
            'body' => $request->input('body'),
            'status' => Comment::IN_PROGRESS,
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
        ]);
        return $this->respondOk('message successfully created');

    }

    /**
     *
     * @return mixed
     */
    public function getInProgressComments()
    {
        return Comment::where('status', '=', Comment::IN_PROGRESS)
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->join('products', 'products.id', '=', 'comments.product_id')
            ->join('buyers', 'users.userable_id', '=', 'buyers.id')
            ->select([
                'name', 'buyers.last_name', 'comments.id as comment_id',
                'comments.body', 'comments.created_at', 'comments.like',
                'products.persian_name', 'products.english_name','products.id as product_id'
            ])
            ->paginate(self::PAGINATE_SIZE);
    }

    /**
     * confirm a specified comment
     *
     * @param $id
     * @return mixed
     */
    public function confirm($id)
    {
        Comment::where('id', $id)->update(['status' => Comment::CONFIRMED]);
        return $this->respondOk("confirmed Ok", "message");
    }

    /**
     * Remove the specified comment from database.
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->auth->user();
        $user = UserPermission::checkOrOfPermissions([UserPermission::STAFF, UserPermission::BUYER]);
        $comment = null;
        if ((new UserPermission())->setUser($user)->isStaff()) {
            UserPermission::checkPermission([Permission::VERIFY_COMMENT]);
            $comment = Comment::where('id', '=', $id)->first();
        } else {//user is buyer
            $comment = Comment::where('user_id', '=', $user->id)->where('id', '=', $id)->first();
        }
        if ($comment === null) {
            return $this->setStatusCode(404)->respondWithError('comment not found');
        }
        $comment->delete();
        return $this->setStatusCode(200)->respondOk('successfully deleted');
    }

    public function rejectComment($id)
    {
        $user = $this->auth->user();
        $comment = null;
        if ((new UserPermission())->setUser($user)->isStaff()) {
            $comment = Comment::where('id', '=', $id)->first();
        } else {//user is buyer
            $comment = Comment::where('user_id', '=', $user->id)->where('id', '=', $id)->first();
        }
        if ($comment === null) {
            return $this->setStatusCode(404)->respondWithError('comment not found');
        }
        $comment->update(['status' => Comment::REJECTED]);
        return $this->setStatusCode(200)->respondOk('successfully rejected');
    }

    private function checkDuplicateLike($commentId)
    {
        $commentLikeCount = CommentLike::where('comment_id', $commentId)->count();
        if ($commentLikeCount === 0) {

        }
    }

    public function contactUs(Requests\ContactUsRequest $request)
    {
        $payload = [
            'response' => $request->input('g-recaptcha-response'),
            'remote_ip' => $request->ip()
        ];
        $this->captcha->setPayload($payload)->verify();
        $client = new Client('https://hooks.slack.com/services/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');
        $message = "نام : ".$request->name."\n ایمیل : ".$request->email."\n شماره تماس : ".$request->phone_number."\n".
            "نظر : ".$request->comment;
        $client->to('#content')->send($message);
        return $this->respondOk();
    }
}