{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>跳转提示</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="MobileOptimized" content="320" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="/plugins/yim_e404/css/dandelion.css"  media="screen" />
</head>
<body>
<div id="da-wrapper" class="fluid">
    <!-- Content -->
    <div id="da-content">
        <!-- Container -->
        <div class="da-container clearfix">
            <div id="da-error-wrapper">
                <div id="da-error-pin"></div>
                <switch name="code">
                    <case value="1">
                        <div id="da-error-code">success <span>☺</span></div>
                    </case>
                    <case value="0">
                        <div id="da-error-code">error <span>☹</span></div>
                    </case>
                    <default />
                    <div id="da-error-code">^-^ <span>{$code}</span></div>
                </switch>
                <h1 class="da-error-heading"><?php echo(strip_tags($msg));?></h1>
                <notempty name="url">
                    <p>页面自动： <a id="href" href="{$url}">跳转</a> 等待时间： <b id="wait">{$wait}</b>
                </notempty>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
