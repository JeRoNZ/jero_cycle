<?php defined('C5_EXECUTE') or die("Access Denied.");

$page = Page::getCurrentPage();
if ($page->isEditMode()) { ?>
	<div class="ccm-edit-mode-disabled-item disabledCycle2Block">
		<div><?php echo t('Cycle2 Block disabled in edit mode.'); ?></div>
	</div>
	<?php
	return;
}
?>

<div class="cycle-slideshow" id="cycle-slideshow<?php echo $bID ?>"
	 data-cycle-swipe="true"
	 data-cycle-pause-on-hover="<?php echo $pause == 1 ? 'true' : 'false' ?>"
	 data-cycle-sync="<?php echo $sync == 1 ? 'true' : 'false' ?>"
	 data-cycle-speed="<?php echo $speed ?>"
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
		?> data-cycle-fx="<?php echo $effect ?>"
		data-cycle-timeout="<?php echo $timeout ?>"
	<?php
	}
	?>
	 data-cycle-log="false"
	 data-cycle-overlay-template='<div class="cycle-caption"><h2><a href="{{link}}">{{title}}</a></h2><h4>{{desc}}</h4><?php
	 if ($button) { ?><a class="<?php echo $buttonCSS ? $buttonCSS : 'btn btn-default'?>" href="{{link}}">{{buttontext}}</a><?php }?></div>'
	<?php
	$ratio = false;
	foreach ($rows as $row) {
		$fo = File::getByID($row['fID']);
		if ($fo) {
			$fv = $fo->getVersion();
			if (!$ratio) {
				$ratio = true;
				?>
				 data-cycle-auto-height="<?php echo $fv->getAttribute('width') ?>:<?php echo $fv->getAttribute('height') ?>">
				<div class="cycle-overlay"></div>
				<?php
				switch ($navigationType) {
					case 1:
					?>
					<div class="cycle-prev"><img src="<?php echo $blockURL?>/img/arrow-left.png" alt="Previous"></div>
					<div class="cycle-next"><img src="<?php echo $blockURL?>/img/arrow-right.png" alt="Next"></div>
				<?php
						break;
					case 2:
						?><div class="cycle-pager"></div><?php
						break;
				}
			}
		?><img data-cycle-title="<?php echo h($row['title']) ?>"
			   data-cycle-desc="<?php echo h($row['description']) ?>"
			   data-cycle-link="<?php
			   if ($row['linkURL'])
			   		echo $row['linkURL'];
			   ?>"
			   data-cycle-buttontext="<?php echo h($row['buttonText'])?>"
			   src="<?php echo $fv->getURL() ?>" alt="<?php echo h($row['title']) ?>"/>
		<?php
		}
	}
	?>
</div>