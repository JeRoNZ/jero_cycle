<?php  defined('C5_EXECUTE') or die("Access Denied.");

$page = Page::getCurrentPage();
if ($page->isEditMode()) {
	?>
	<div class="ccm-edit-mode-disabled-item disabledCycle2Block">
		<div><?php  echo t('Cycle2 Block disabled in edit mode.'); ?></div>
	</div>
	<?php 
	return;
}
?>

<div class="cycle-slideshow" id="cycle-slideshow<?php  echo $bID ?>"
	 data-cycle-swipe="true"
	 data-cycle-pause-on-hover="<?php  echo $pause == 1 ? 'true' : 'false' ?>"
	 data-cycle-sync="<?php  echo $sync == 1 ? 'true' : 'false' ?>"
	 data-cycle-speed="<?php  echo $speed ?>"
	 <?php 
	 if ($noAnimate == 1) {
	 ?>data-cycle-paused="true"<?php 
}
if ($effect == 'continuous') {
	?>
	data-cycle-easing="linear"
	data-cycle-fx="scrollHorz"
	data-cycle-timeout="1"
<?php 
} else {
	?> data-cycle-fx="<?php  echo $effect ?>"
	data-cycle-timeout="<?php  echo $timeout ?>"
<?php 
}
?>
	 data-cycle-log="false"
	<?php  if ($fadeCaption) { ?>
		data-cycle-caption-plugin="caption2"
		data-cycle-overlay-fx-out="fadeOut"
		data-cycle-overlay-fx-in="fadeIn"
	<?php  } ?>
	data-cycle-max-z="<?php  echo $maxZ?>"

	 data-cycle-overlay-template='<div class="cycle-caption" style="z-index:<?php  echo $maxZ ? $maxZ +10 : 110?>">{{h2link}}<h4>{{desc}}</h4><span class="<?php  echo $buttonCSS ? $buttonCSS : 'btn btn-default' ?>"><a class="{{hiddenclass}}" href="{{link}}">{{buttontext}}</a></span></div>'
	 data-cycle-auto-height="calc">
	<div class="cycle-overlay"></div>
	<?php 
	switch ($navigationType) {
		case 1:
			?>
			<div class="cycle-prev" style="z-index:<?php  echo $maxZ ? $maxZ +11 : 111?>"><img src="<?php  echo $blockURL ?>/img/arrow-left.png" alt="Previous"></div>
			<div class="cycle-next" style="z-index:<?php  echo $maxZ ? $maxZ +11 : 111?>"><img src="<?php  echo $blockURL ?>/img/arrow-right.png" alt="Next"></div>
			<?php 
			break;
		case 2:
			?>
			<div class="cycle-pager" style="z-index:<?php  echo $maxZ ? $maxZ +11 : 111?>"></div>
			<?php 
			break;
	}
	$first = false;
	foreach ($rows as $row) { ?>
		<img <?php
			if ($first) echo 'style="display:none" ';
			$first = true;
			?>
			data-cycle-title="<?php  echo h($row['title']) ?>"
			 data-cycle-desc="<?php  echo h($row['description']) ?>"
			<?php 
			if ($row['linkURL']) {
				?>
				data-cycle-link="<?php  echo $row['linkURL'] ?>"
				data-cycle-h2link='<h2><a href="<?php  echo $row['linkURL'] ?>"><?php  echo h($row['title']) ?></a></h2>'
			<?php 
			} else {
				?>
				data-cycle-link="#"
				data-cycle-hiddenclass="cycle-link-hidden"
				data-cycle-h2link='<h2><?php  echo h($row['title']) ?></h2>'
			<?php 
			}
			if ($row['buttonText']) {
				?>
				data-cycle-buttontext="<?php  echo h($row['buttonText']) ?>"
			<?php  } else { ?>
			data-cycle-hiddenclass="cycle-link-hidden"
			<?php  } ?>
			 src="<?php  echo($row['fV'] ? $row['fV']->getURL() : '') ?>" alt="<?php  echo h($row['title']) ?>"/>
	<?php } ?>
</div>
