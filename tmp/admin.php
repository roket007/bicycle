<?php $this->renderHeaders(); ?>
<?php $this->setBlocks( array('adminmenu') ); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
        <meta http-equiv="Cache-Control" content="no-cache" />
        <?php if( strpos($_SERVER['REQUEST_URI'], 'staticpage') === false && strpos($_SERVER['REQUEST_URI'], 'projects') === false ) : ?>
            <script src="/js/jquery.js"></script>
        <?php endif; ?>
        <style>
            *{
                font-family: arial;
            }
            
            html {
                min-width: 700px;
            }
        </style>
        <?php $this->renderHead(); ?>
    </head>
    <body>
        <?php if( isset($this->blocks['adminmenu']) ) : ?>
            <?php $this->blocks['adminmenu']->render() ; ?>
        <?php endif; ?>
        <?php $this->renderContent(); ?>
    </body>
</html>