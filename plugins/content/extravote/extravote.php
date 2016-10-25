<?php
// No direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
class plgContentExtraVote extends JPlugin
{
	protected $article_id;
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();		
		$this->view = JRequest::getCmd('view');
	}		
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart = 1)
	{
		if (strpos($context, 'com_content') !== false) {
				$this->article_id = $article->id;
				$this->ExtraVotePrepare($article, $params);	
				if ( $this->params->get('display') == 0  )
			{
				$hide  = $this->params->get('hide', 1);
					if ( $hide != 1 || $this->view == 'article' ) {
					$article->xid = 0;
					return $this->ContentExtraVote($article, $params);
				}
			}
		}
	}	
	protected function ContentExtraVote(&$article, &$params)
	{ 		
		$rating_count=$rating_sum=0;
		$html='';
		if ($params->get('show_vote'))
		{
			$db	= JFactory::getDBO();
			$query='SELECT * FROM #__content_extravote WHERE content_id='.$this->article_id.' AND extra_id = 0';
			$db->setQuery($query);
			$vote=$db->loadObject();		
			if($vote) {
				$rating_sum = $vote->rating_sum;
				$rating_count = intval($vote->rating_count);
			}		
				$html .= $this->plgContentExtraVoteStars( $this->article_id, $rating_sum, $rating_count, $article->xid );
		}
		return $html;
 	}  
 	protected function plgContentExtraVoteStars( $id, $rating_sum, $rating_count, $xid )
	{
		$document = JFactory::getDocument();
		if ( $this->params->get('css', 1) ) :
			$document->addStyleSheet(JURI::root(true).'/plugins/content/extravote/assets/extravote.css');
		endif;		
		$document->addScript(JURI::root(true).'/plugins/content/extravote/assets/extravote.js');
     	global $plgContentExtraVoteAddScript;
		$show_counter = $this->params->get('show_counter',1);
		$show_rating  = $this->params->get('show_rating',1);
		$rating_mode  = $this->params->get('rating_mode', 1);
		$show_unrated = $this->params->get('show_unrated',1);
		$rating  = 0;
	 	if(!$plgContentExtraVoteAddScript){ 
         	$document->addScriptDeclaration("
				var ev_basefolder = '".JURI::base(true)."';
				var extravote_text=Array('".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_MESSAGE_NO_AJAX')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_MESSAGE_LOADING')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_MESSAGE_THANKS')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_MESSAGE_LOGIN')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_MESSAGE_RATED')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_LABEL_VOTES')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_LABEL_VOTE')."','".
					JTEXT::_('PLG_CONTENT_EXTRAVOTE_LABEL_RATING').
				"');
			");
     		$plgContentExtraVoteAddScript = 1;
	 	}
		if( $rating_count!=0 ) {
			$rating  = ($rating_sum / intval( $rating_count ));
		} elseif( $show_unrated == 0 ) {
			$show_counter = -1;
			$show_rating  = -1;
		}
		$container = 'div';
		$class     = 'extravote';
		if( (int)$xid ) { 
			if ( $show_counter == 2 ) $show_counter = 0;
			if ( $show_rating == 2 ) $show_rating = 0;
			$container = 'span';
			$class     = 'extravote-small';
		} else {
			if ( $show_counter == 3 ) $show_counter = 0;
			if ( $show_rating == 3 ) $show_rating = 0;
		}
		$stars = $this->params->get('stars',10);
		$spans = '';
		for ($i=0,$j=5/$stars; $i<$stars; $i++,$j+=5/$stars) :
			$spans .= "
      <span class=\"extravote-star\"><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",".$j.",".$rating_sum.",".$rating_count.",'".$xid."',".$show_counter.",".$show_rating.",".$rating_mode.");\" title=\"".JTEXT::_('PLG_CONTENT_EXTRAVOTE_RATING_'.($j*10).'_OUT_OF_5')."\" class=\"ev-".($j*10)."-stars\">1</a></span>";
		endfor;
	 	$html = "
<".$container." class=\"".$class."\">
  <span class=\"extravote-stars\">
    <span id=\"rating_".$id."_".$xid."\" class=\"current-rating\" style=\"width:".round($rating*20)."%;\"></span>"
	.$spans."
  </span>
  <span class=\"extravote-info\" id=\"extravote_".$id."_".$xid."\">";
  		if ( $show_rating > 0 ) {
			if ( $rating_mode == 0 ) {
				$rating = round($rating*20) . '%';	
			} else {
				$rating = number_format($rating,2);	
			}
			$html .= JTEXT::sprintf('PLG_CONTENT_EXTRAVOTE_LABEL_RATING', $rating);
		}
  		if ( $show_counter > 0 ) {
			if($rating_count!=1) {
				$html .= JTEXT::sprintf('PLG_CONTENT_EXTRAVOTE_LABEL_VOTES', $rating_count);
			} else { 
				$html .= JTEXT::sprintf('PLG_CONTENT_EXTRAVOTE_LABEL_VOTE', $rating_count);
			}
		}
 	 	$html .="</span>";
 	 	$html .="
</".$container.">";
	 	return $html;
 	}
 	protected function ExtraVotePrepare( $article, &$params ) 
	{
	    if (isset($this->article_id)) {
	        $extra = $this->params->get('extra', 1);
			$main  = $this->params->get('main', 1);
 	 	    if ( $extra != 0 ) {
   	 		    $regex = "#{extravote\s*([0-9]+)}#s";
			if ( $this->view != 'article' ) {
					if ( $extra == 2 ) {
						$article->introtext = preg_replace( $regex, '', $article->introtext );	
					} else {
						$article->introtext = preg_replace_callback( $regex, array($this,'plgContentExtraVoteReplacer'), $article->introtext );	
					}
				} else {
//				    $this->article_id = $article->id;
   	 			    $article->text = preg_replace_callback( $regex, array($this,'plgContentExtraVoteReplacer'), $article->text );
			    }
		    }
 	 	    if ( $main != 0 ) {
				if ( $main == 2 && $this->view != 'article')
			    {
   	 			    $article->introtext = preg_replace( '#{mainvote}#', '', $article->introtext );
			    } else {
				    $this->article_id = $article->id;
					if ( $this->view == 'article' ) {
   	 			    	$article->text = preg_replace_callback( '#{mainvote}#', array($this,'plgContentExtraVoteReplacer'), $article->text );
					} else {
   	 			    	$article->introtext = preg_replace_callback( '#{mainvote}#', array($this,'plgContentExtraVoteReplacer'), $article->introtext );
					}
			    }
		    }
		    if ( $this->params->get('display') == 1 )  {
		        $article->xid = 0;
				if ( $this->view == 'article' ) {
			        $article->text .= $this->ContentExtraVote($article, $params);
				} elseif ( $this->params->get('hide') == 0 ) {
			        $article->introtext .= $this->ContentExtraVote($article, $params);
				}
		    }
 	    }
 	}
 	protected function plgContentExtraVoteReplacer(&$matches ) 
	{
  		$db	 = JFactory::getDBO();
		$xid = 0;
		if (isset($matches[1])) {
			$xid = (int)$matches[1];
		}
  		$cid = 0;
		if ( $this->params->get('article_id') || $xid == 0 ) {
			$cid = $this->article_id;
		}
  		$rating_sum = 0;
  		$rating_count = 0;
		$db->setQuery('SELECT * FROM #__content_extravote WHERE content_id='.(int)$cid.' AND extra_id='.(int)$xid);
  		$vote = $db->loadObject();
  		if($vote) {
	 		if($vote->rating_count!=0)
				$rating_sum = $vote->rating_sum;
				$rating_count = intval($vote->rating_count);
	 	}
		if ( $xid == 0 ) :
			global $extravote_mainvote;
			$extravote_mainvote .= 'x';
			$xid = $extravote_mainvote;
		endif;  		
		return $this->plgContentExtraVoteStars( $cid, $rating_sum, $rating_count, $xid );
	}	
}