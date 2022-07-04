<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
</head>

<body onLoad='callSubmit()'>
<form action={{$transaction_link}} method="POST" id="transactionMiddle" >

    @foreach($params as $a)
        <input type="hidden" name={{$a['name']}} value={{$a['value']}} />
    @endforeach
</form>
<script>
    function callSubmit(){
        document.getElementById("transactionMiddle").submit();
    }
</script>
</body>

</html>

