<?php defined('C5_EXECUTE') or die("Access Denied.");

$page = Page::getCurrentPage();

$ih = Core::make('helper/image');
/* @var $ih \Concrete\Core\File\Image\BasicThumbnailer */

if ($page->isEditMode()) {
	?>
	<div class="ccm-edit-mode-disabled-item disabledCycle2Block">
		<div><?php echo t('Cycle2 Block (Carousel Template) disabled in edit mode.'); ?></div>
	</div>
	<?php
	return;
}
?>

<div class="cycle-slideshow carousel" id="cycle-slideshow<?= $bID ?>"
	 data-cycle-fx="carousel"
	 data-cycle-timeout="<?= $timeout ?>"
	 data-cycle-log="true"

	 data-cycle-carousel-visible="4"
	 data-cycle-carousel-fluid="true"

	 data-cycle-pause-on-hover="<?= $pause == 1 ? 'true' : 'false' ?>"
	 data-cycle-paused="<?= $noAnimate ? 'true' : 'false' ?>"

	 data-cycle-max-z="<?= $maxZ ?>">

	<div class="cycle-prev" style="z-index:<?php echo $maxZ ? $maxZ + 11 : 111 ?>"><i class="fa fa-chevron-left"></i></div>
	<div class="cycle-next" style="z-index:<?php echo $maxZ ? $maxZ + 11 : 111 ?>"><i class="fa fa-chevron-right"></i></div>
	<?php
	foreach ($rows as $row) {
		$img = false;
		if (is_object($row['fo'])){
			$img = $ih->getThumbnail($row['fo'], 374, 400, true);
		}
		?>
		<img
			data-cycle-title="<?php echo h($row['title']) ?>"
			src="<?= $img->src ?>" alt="<?php echo h($row['title']) ?>"/>
	<?php } ?>
</div>

<script type="text/javascript">
	$(window).resize(function(){
		console.log('Resized');
		$('#cycle-slideshow<?= $bID ?>').cycle('reinit');
	});
</script>