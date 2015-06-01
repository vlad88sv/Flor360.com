<head>
    <script src="/JS/jquery-1.7.2.js" type="text/javascript"></script>
</head>
<html>
<body>
<audio id="beep">
    <source src="/SND/bb.wav">
    <source src="/SND/bb.mp3">
</audio>

<script type="text/javascript">    
    function Beep() {
        $('#beep').get(0).play();
    }
</script>
</body>
</html>