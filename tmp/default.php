<?php defined('ANIPORT') || die; ?>
<?php $this->renderHeaders(); ?>
<?php $this->setBlocks( array('menu', 'menufooter') ); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
        <!--[if lt IE 9]>
            <script src="/js/html5.js"></script>
        <![endif]-->
        <link rel="stylesheet" href="/css/template.css" />
        <!--[if (gt IE 8) | (IEMobile)]><!-->
            <link rel="stylesheet" href="/css/template.css" />
        <!--<![endif]-->
        <!--[if (lt IE 9) & (!IEMobile)]>
            <link rel="stylesheet" href="/css/ie.css" />
        <![endif]-->
        <script src="/js/jquery.js"></script>
        <script src="/js/html5.js"></script>
        <script src="/js/custom.js"></script>
        <script src="/js/jquery.sliderkit.1.9.2.pack.js"></script>
        <!--<script src="/js/jquery.tools.min.js"></script>-->
        <link rel="stylesheet" href="/css/custom.css" />
        <link href='http://fonts.googleapis.com/css?family=Scada&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
        <?php $this->renderHead(); ?>
    </head>
    <body>
        <div id="top">
            <div class="grid-container"> 
                <div class="grid-100 mobile-grid-100 tablet-grid-100"> 
                    <div id="left_top" class="grid-50 mobile-grid-50 tablet-grid-50">
                        <span class="date hide-on-mobile"><?= date("d.m.Y", time() ) ?></span>
                        <div id="language">
                            <img alt="" src="/img/ua_flag.png" width="20" height="20" />
                            <?php if(Fw_Request::get('language') == 'ua') : ?>
                                УКР 
                            <?php else : ?>
                                <a href="<?= ( isset($this->ua_lang_url) ? $this->ua_lang_url : C::getCurrentUriByLang() ) ?>" title="Українська версія">УКР</a>
                            <?php endif; ?>
                            <img alt="" src="/img/rus_flag.png" width="20" height="20" /> 
                            <?php if(Fw_Request::get('language') == 'ru') : ?>
                                РУС 
                            <?php else : ?>
                                <a href="<?= ( isset($this->ru_lang_url) ? $this->ru_lang_url : C::getCurrentUriByLang('ru') ) ?>" title="Российская версия сайта">РУС</a>
                            <?php endif; ?>
                        </div>   
                    </div>
                    <div id="right_top" class="grid-50 mobile-grid-50 tablet-grid-50">
                        <?php if( Fw_Auth::isValidAdmin() ) : ?>
                            <a href="/admin/">Панель администратора</a>
                        <?php endif; ?>
                        <img alt="" src="/img/icon_register.png" width="20" height="20">
                        <?php if( !Fw_Auth::isValid() ) : ?>
                            <a href="<?= (C::$lang == 'ua' ? '' : '/ru'); ?>/user/registration/" title="<?= C::getLanguageString('register_new_user') ?>"><?= C::getLanguageString('registration') ?></a> 
                        <?php else : ?>
                            <a href="<?= (C::$lang == 'ua' ? '' : '/ru'); ?>/donors/<?= Fw_Auth::get('id') ?>/" title="<?= C::getLanguageString('profile') ?>"><?= C::getLanguageString('profile') ?></a>
                        <?php endif; ?>
                        
                        <img alt="" src="/img/icon_entering.png" width="20" height="20">
                        <?php if( !Fw_Auth::isValid() ) : ?>
                            <a href="javascript:void(0);" id="show-login-form" title="<?= C::getLanguageString('enter') ?>"><?= C::getLanguageString('enter') ?></a>
                            <div class="top-login-user" style="position: absolute; z-index: 6000;">
                                <div class="login-user" style="display: none;">
                                    <form action="<?= (C::$lang == 'ua' ? '' : '/ru'); ?>/user/login/" method="post">
                                        <div><input name="login" type="text" placeholder="<?= C::getLanguageString('login') ?>" /></div>
                                        <div><input name="password" type="password" placeholder="<?= C::getLanguageString('password') ?>" /></div>
                                        <div><input type="submit" value="<?= C::getLanguageString('entering') ?>" /></div>
                                        <input type="hidden" name="referer" value="<?= base64_encode($_SERVER['REQUEST_URI']) ?>" />
                                    </form>
                                </div>
                            </div>
                            <script type="text/javascript">
                                $('.top-login-user').css('margin-left', ( $('#right_top').width() - 180 ) );
                            </script>
                        <?php else : ?>
                            <a href="<?= (C::$lang == 'ua' ? '' : '/ru'); ?>/user/logout/" title="<?= C::getLanguageString('logout') ?>"><?= C::getLanguageString('logout') ?></a>
                        <?php endif; ?> 
                    </div>
                </div>
            </div>
        </div>
        <header class="grid-container">
            <div class="grid-100 mobile-grid-100 tablet-grid-100"> 
                <div id="logo" class="grid-30 mobile-grid-100 tablet-grid-25">
                    <a href="/<?= C::$lang ?>/" title="<?= C::getLanguageString('main_slogon') ?>"><img alt="<?= C::getLanguageString('main_slogon_more') ?>" src="/img/logo_lovechernivtsi.png" width="210" height="23"></a> <br> <span><?= C::getLanguageString('main_slogan_then') ?></span> 
                </div>
        <?php $this->blocks['menu']->render(); ?>
            </div>
        <!--SLIDER-->
        <?php if( isset($this->blocks['slider']) ) : ?>
            <?php $this->blocks['slider']->render() ; ?>
        <?php endif; ?>
        <!--END-SLIDER-->
        <?php if( isset($this->blocks['moneycount']) ) : ?>
            <?php $this->blocks['moneycount']->render() ; ?>
        <?php endif; ?>
        </header>
        <?php if( isset($this->blocks['breadcrumbs']) ) : ?>
            <?php $this->blocks['breadcrumbs']->render() ; ?>
        <?php endif; ?>
        <?php $this->renderContent(); ?>
        <!--FOOTER-->
        <footer class="hide-on-mobile"> 
            <div class="grid-container"> 
                <?php $this->blocks['menufooter']->render(); ?>
            </div>
        </footer>
        <!--END-FOOTER-->
        <!--COPYRIGHT-->
        <div id="copyright" class="grid-container">
            <div class="grid-100 mobile-grid-100 tablet-grid-100"> 
                <div class="grid-50 mobile-grid-50 tablet-grid-50"> 
                    <?= C::getLanguageString('copyright') ?>
                </div>	
                <div class="grid-50 mobile-grid-50 tablet-grid-50 social">
                    <?= C::getLanguageString('soc') ?> <a href="http://vk.com/lovechernivtsi" title="Lovechernivtsi ВКонтакте" target="_blank"><img alt="" src="/img/vkontakte.png" width="25" height="25"></a> 
                    <a href="https://www.facebook.com/lovechernivtsi" title="Lovechernivtsi на Facebook" target="_blank"><img alt="" src="/img/facebook.png" width="25" height="25"></a> 
                    <a href="https://www.twitter.com/lovechernivtsi" title="Lovechernivtsi в Twitter" target="_blank"><img alt="" src="/img/twitter.png" width="25" height="25"></a> 
                </div>
            </div>
        </div>
        <!--END-COPYRIGHT-->
    </body>
</html>