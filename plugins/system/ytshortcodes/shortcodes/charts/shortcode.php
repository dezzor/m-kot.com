<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function chartsYTShortcode($atts,$content = null)
{
	global $type_change_chart;
	extract(ytshortcode_atts( array(
		'type_change' =>'',
		'width'=>'100',
		'labels_line'=>'',
		'labels_bar'=>'',
		'labels_radar'=>''
	),$atts));
	$type_change_chart = $type_change;
	if(isset($labels_line) && ($labels_line !=''))
	{
		$labels = $labels_line;
	}
	if(isset($labels_bar) && ($labels_bar !=''))
	{
		$labels = $labels_bar;
	}
	if(isset($labels_radar) && ($labels_radar !=''))
	{
		$labels = $labels_radar;
	}
	$idcanvas = uniqid('canvas_').rand().time();
	$script ='';
	$return ='<div class="yt-clearfix" style="width:'.$width.'%">
			<div>
				<canvas id="'.$idcanvas.'" height="450" width="600"></canvas>
			</div>
		</div>';
	
	$str='';
	$name_str='';
	
	//TH1
	if(strtolower($type_change)=='doughnut' || strtolower($type_change)=='pie' || strtolower($type_change)=='polar')
	{
		$str = parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
	}
	//TH2
	else
	{
		$labelsArray = explode(',',trim($labels));
		if(count($labelsArray) >1)
		{
			$labelsArr = '';
			foreach($labelsArray as $item )
			{
				$labelsArr .= "\"".$item."\",";
			}
			$datasets = '';
			$datasets .= parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
			$str='labels : ['.rtrim($labelsArr,',').'],
				datasets : [
					'.$datasets.'
				]';
		}
		else
		{
			$return .= yt_alert_box('Charts not work, Please select the correct source and settings.', 'warning');
		}
	}
	switch(strtolower($type_change))
	{
		case 'line':
			$script .='var lineChartData = {
					'.$str.'
				}

			function charts_line(){
				var ctx'.$idcanvas.' = document.getElementById("'.$idcanvas.'").getContext("2d");
				window.myLine = new Chart(ctx'.$idcanvas.').Line(lineChartData, {
					responsive: true

				});
			}
			charts_line();';
		break;
		case 'bar':
			$script .='var barChartData = {
						'.$str.'
					}
					function charts_bar(){
						var ctx = document.getElementById("'.$idcanvas.'").getContext("2d");
						window.myBar = new Chart(ctx).Bar(barChartData, {
							responsive : true
						});
					}
					charts_bar();';

				break;
		case 'doughnut':
		$script .='var doughnutData = [
							'.$str.'
						];
						function charts_doughnut() {
							var ctx = document.getElementById("'.$idcanvas.'").getContext("2d");
							window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {responsive : true});
						};
						charts_doughnut();';
		break;
		case 'pie':
		$script .='var PieData = [
							'.$str.'
						];
						function charts_pie(){
							var ctx = document.getElementById("'.$idcanvas.'").getContext("2d");
							window.myDoughnut = new Chart(ctx).Pie(PieData, {responsive : true});
						};
						charts_pie();';
		break;
		case 'polar':
		$script .='var polarData = [
							'.$str.'
						];
						function charts_polar(){
							var ctx = document.getElementById("'.$idcanvas.'").getContext("2d");
							window.myDoughnut = new Chart(ctx).PolarArea(polarData, {responsive : true});
						};
						charts_polar();';
		break;
		case 'radar':
		$script .='var radarChartData = {
				'.$str.'
			}
			function charts_bar(){
				var ctx = document.getElementById("'.$idcanvas.'").getContext("2d");
				window.myBar = new Chart(ctx).Radar(radarChartData, {
					responsive : true
				});
			}
			charts_bar();';
		break;
	}
	JHtml::_('jquery.framework');
	JHtml::script(JUri::base()."plugins/system/ytshortcodes/shortcodes/charts/js/Chart.min.js");
	$html = '<script>';
	$html .= $script;
	$html .= '</script>';
	return $return.$html;
}
?>