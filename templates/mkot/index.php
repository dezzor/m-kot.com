<?php
 defined('_JEXEC') or die;
 JLoader::import('joomla.filesystem.file');
 $doc = JFactory::getDocument();

 $doc->setMetaData( 'generator', 'build in studio IT-Simvol.RU' );



 $doc->addStyleSheet($this->baseurl . '/templates/system/css/system.css');
 $doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/bootstrap.css', $type = 'text/css', $media = 'screen,projection');
 $doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/template.css', $type = 'text/css', $media = 'screen,projection');
 $doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/js/slick/slick.css', $type = 'text/css', $media = 'screen,projection');
 $doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/js/slick/slick-theme.css', $type = 'text/css', $media = 'screen,projection');
 $doc->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/mobile.css', $type = 'text/css', $media = 'screen,projection');

 //JHtml::_('jquery.framework');
 //$doc->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', 'text/javascript');
 $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/bootstrap.min.js', 'text/javascript');
 $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/slick/slick.js', 'text/javascript');
 $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/script.js', 'text/javascript');

?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <jdoc:include type="head" />
</head>
<body>

  <header>
    <div class="wrap-no-intim">
      <h1 class="wrap-no-intim__text"><?php echo JText::_('WARN_AGE'); ?></h1>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-md-3" id="logo"><jdoc:include type="modules" name="logo" style="none" /></div>
        <div class="col-md-5">
          <address><?php echo JText::_('ADRESS'); ?></address>
          <div class="phone"><?php echo JText::_('TELEPHONE'); ?></div>
          <div class="metro"><?php echo JText::_('METRO'); ?></div>
        </div>
        <div class="col-md-4 hidden-xs">
          <div class="row ">
            <div class="col-md-2"></div>
            <div class="col-md-8 "><div class="row"><jdoc:include type="modules" name="rest" style="none" /></div></div>
            <div class="col-md-2"></div>
          </div>
          <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
              <div class="row">
                <div class="col-md-6 no-padding"><div class="row"><jdoc:include type="modules" name="social" style="none" /></div></div>
                <div class="col-md-6"><div class="row"><jdoc:include type="modules" name="position-3" style="none" /></div></div>
              </div>
            </div>
            <div class="col-md-3"></div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="navbar navbar-inverse" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Навигация</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#"></a>
      </div>
      <div class="navbar-collapse collapse"><jdoc:include type="modules" name="position-4" style="none" /></div>
    </div>
  </div>

  <jdoc:include type="modules" name="mail" style="" />

  <jdoc:include type="modules" name="content-top" style="xhtml" />

  <jdoc:include type="modules" name="video" style="html5" />

  <section>
    <div class="container">
      <jdoc:include type="modules" name="breadcrumbs" style="none" />
      <jdoc:include type="message" />
      <jdoc:include type="component" />
    </div>
  </section>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-3 footer-social">
          <jdoc:include type="modules" name="social-footer" style="none" />
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-6">
          <p class="orange"><?php echo JText::_('EROS_MASSAGE'); ?></p>
          <p class="right-shift"><?php echo JText::_('EROS_MASSAGE2'); ?></p>
          <br>
          <address><?php echo JText::_('ADRESS'); ?></address>
          <div class="phone"><?php echo JText::_('TELEPHONE'); ?></div>
          <p class="right-shift"><?php echo JText::_('METRO'); ?></p>
        </div>
      </div>
    </div>
    <div class="footer-slogan">
      <?php echo JText::_('FOOTER_TEXT'); ?>
    </div>

    <a href="#top" id="back-top" onclick="return up()"></a>
  </footer>


  <div style="display:none;">
  <!--LiveInternet counter--><script type="text/javascript"><!--
    document.write("<a href='//www.liveinternet.ru/click' "+"target=_blank><img src='//counter.yadro.ru/hit?t45.7;r"+escape(document.referrer)+((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+";h"+escape(document.title.substring(0,80))+";"+Math.random()+"' alt='' title='LiveInternet' "+"border='0' width='31' height='31'><\/a>")
  //--></script><!--/LiveInternet-->
  </div>
  <!-- Yandex.Metrika counter --> <script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter21070918 = new Ya.Metrika({ id:21070918, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/21070918" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
</body>
</html>