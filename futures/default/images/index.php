<?php
$full_domain = $_SERVER['SERVER_NAME'];
$just_domain = preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $_SERVER['HTTP_HOST']);
?>
<script language=javascript>
setTimeout("location.href='http://<?php echo $just_domain;?>'", 0);
</script>