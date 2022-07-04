<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:37
 */

namespace Wego\Transforms;


use League\Fractal\TransformerAbstract;
use PhpParser\Comment;

class CommentTransformer extends TransformerAbstract
{

    use TransformerHelper;
    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Comment $comment)
    {
        return $this->transformWithFieldFilter([
            'status' => $comment->status,
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'product_id' => $comment->product_id,
            'body' => $comment->body,
            'like' => $comment->like
        ], $this->fields);
    }
}