<?php defined('C5_EXECUTE') or die("Access Denied.");

$fp = new Permissions(FileSet::getGlobal());
$tp = new Permissions();

use Concrete\Core\Application\Service\UserInterface;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Utility\Service\Identifier;
$app = Application::getFacadeApplication();
/** @var UserInterface $userInterface */
$userInterface = $app->make(UserInterface::class);
/** @var Identifier $id */
$idHelper = $app->make(Identifier::class);

$id = $idHelper->getString(18);

$v9 = version_compare(\Config::get('concrete.version'), '9.0', '>=');

if ($v9) {
	echo $userInterface->tabs([
		['slides-' . $id, t('Slides'), true],
		['options-' . $id, t('Options')],
		['about-' . $id, t('About')],
	]);
} else {
	echo Core::make('helper/concrete/ui')->tabs(array(
		array('slides', t('Slides'), true),
		array('options', t('Options')),
		array('about', t('About'))
	));
}
?>

<script>
	var CCM_EDITOR_SECURITY_TOKEN = "<?php echo Core::make('helper/validation/token')->generate('editor'); ?>";

	<?php
		if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {
		$editorJavascript = Core::make('editor')->outputStandardEditorInitJSFunction();
		?>
	var launchEditor = <?= $editorJavascript; ?>;
	<?php
		}
	?>

	$(document).ready(function() {
		var sliderEntriesContainer = $('.ccm-image-slider-entries');
		var _templateSlide = _.template($('#imageTemplate').html());

		var attachDelete = function($obj) {
			$obj.click(function() {
				var deleteIt = confirm('<?php echo t('Are you sure?'); ?>');
				if (deleteIt === true) {
					<?php if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {?>
					var slideID = $(this).closest('.ccm-image-slider-entry').find('.editor-content').attr('id');
					if (typeof CKEDITOR === 'object') {
						CKEDITOR.instances[slideID].destroy();
					}
					<?php } ?>
					$(this).closest('.ccm-image-slider-entry').remove();
					doSortCount();
				}
			});
		};

		var attachFileManagerLaunch = function($obj) {
			$obj.click(function() {
				var oldLauncher = $(this);
				ConcreteFileManager.launchDialog(function(data) {
					ConcreteFileManager.getFileDetails(data.fID, function(r) {
						jQuery.fn.dialog.hideLoader();
						var file = r.files[0];
						oldLauncher.html(file.resultsThumbnailImg);
						oldLauncher.next('.image-fID').val(file.fID);
					});
				});
			});
		};

		var attachFileManagerLaunchIcon = function($obj) {
			$obj.click(function() {
				var oldLauncher = $(this);
				ConcreteFileManager.launchDialog(function(data) {
					ConcreteFileManager.getFileDetails(data.fID, function(r) {
						jQuery.fn.dialog.hideLoader();
						var file = r.files[0];
						oldLauncher.html(file.resultsThumbnailImg);
						oldLauncher.next('.icon-fID').val(file.fID);
					});
				});
			});
		};

		var doSortCount = function() {
			$('.ccm-image-slider-entry').each(function(index) {
				$(this).find('.ccm-image-slider-entry-sort').val(index);
			});
		};

		sliderEntriesContainer.on('change', 'select[data-field=entry-link-select]', function() {
			var container = $(this).closest('.ccm-image-slider-entry');
			switch (parseInt($(this).val())) {
				case 2:
					container.find('div[data-field=entry-link-page-selector]').addClass('hide-slide-link').removeClass('show-slide-link');
					container.find('div[data-field=entry-link-url]').addClass('show-slide-link').removeClass('hide-slide-link');
					break;
				case 1:
					container.find('div[data-field=entry-link-url]').addClass('hide-slide-link').removeClass('show-slide-link');
					container.find('div[data-field=entry-link-page-selector]').addClass('show-slide-link').removeClass('hide-slide-link');
					break;
				default:
					container.find('div[data-field=entry-link-page-selector]').addClass('hide-slide-link').removeClass('show-slide-link');
					container.find('div[data-field=entry-link-url]').addClass('hide-slide-link').removeClass('show-slide-link');
					break;
			}
		});

		<?php if ($rows) {
			foreach ($rows as $row) {
				$linkType = 0;
				if ($row['linkURL']) {
					$linkType = 2;
				} else if ($row['internalLinkCID']) {
					$linkType = 1;
			   } ?>
		sliderEntriesContainer.append(_templateSlide({
			fID: '<?php echo $row['fID']; ?>',



			<?php
			 $file = File::getByID($row['fID']);
			 if ($file) {
				echo "image_url: '". $file->getThumbnailURL('file_manager_listing'). "',";
			 	echo "image_dimensions: '".$file->getAttribute('width'). "x".$file->getAttribute('height')."',";
			 	echo "image_title: '".h($file->getTitle())."',";
			 } else { ?>
			image_url: '',
			image_dimensions: '',
			image_title: '',
			<?php } ?>



			iconfID: '<?php echo $row['iconfID']; ?>',
			<?php
			$file = File::getByID($row['iconfID']);
			if ($file) {
				echo "icon_url: '". $file->getThumbnailURL('file_manager_listing'). "',";
				echo "icon_dimensions: '".$file->getAttribute('width'). "x".$file->getAttribute('height')."',";
				echo "icon_title: '". h($file->getTitle()) ."',";
			} 	else { ?>
			icon_url: '',
			icon_dimensions: '',
			icon_title: '',
			<?php } ?>

			link_url: '<?php echo $row['linkURL']; ?>',
			link_type: '<?php echo $linkType; ?>',
			title: '<?php echo addslashes(h($row['title'])); ?>',
			description: '<?php echo str_replace(array("\t", "\r", "\n"), "", addslashes(h($row['description']))); ?>',
			buttonText: '<?php echo h($row['buttonText']); ?>',
			sort_order: '<?php echo $row['sortOrder']; ?>'
		}));
		sliderEntriesContainer.find('.ccm-image-slider-entry:last-child div[data-field=entry-link-page-selector]').concretePageSelector({
			'inputName': 'internalLinkCID[]', 'cID': <?php if ($linkType == 1) { ?><?php echo intval($row['internalLinkCID']); ?><?php } else { ?>false<?php } ?>
		});
		<?php }
	} ?>

		doSortCount();
		sliderEntriesContainer.find('select[data-field=entry-link-select]').trigger('change');

		$('.ccm-add-image-slider-entry').click(function() {
			var thisModal = $(this).closest('.ui-dialog-content');
			sliderEntriesContainer.append(_templateSlide({
				fID: '',
				iconfID: '',
				title: '',
				link_url: '',
				cID: '',
				description: '',
				link_type: 0,
				sort_order: '',
				image_url: '',
				icon_url: '',
				icon_title: '',
				image_dimensions: '',
				image_title: '',
				buttonText: ''
			}));

			$('.ccm-image-slider-entry').not('.slide-closed').each(function() {
				$(this).addClass('slide-closed');
				var thisEditButton = $(this).closest('.ccm-image-slider-entry').find('.btn.ccm-edit-slide');
				thisEditButton.text(thisEditButton.data('slideEditText'));
			});
			var newSlide = $('.ccm-image-slider-entry').last();
			var closeText = newSlide.find('.btn.ccm-edit-slide').data('slideCloseText');
			newSlide.removeClass('slide-closed').find('.btn.ccm-edit-slide').text(closeText);

			thisModal.scrollTop(newSlide.offset().top);

			<?php
			if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {?>
			launchEditor(newSlide.find('.editor-content'));
			<?php } else {?>
			newSlide.find('.redactor-content').redactor({
				minHeight: 200,
				'concrete5': {
					filemanager: <?php echo $fp->canAccessFileManager(); ?>,
					sitemap: <?php echo $tp->canAccessSitemap(); ?>,
					lightbox: true
				}
			});
			<?php } ?>

			attachDelete(newSlide.find('.ccm-delete-image-slider-entry'));
			attachFileManagerLaunch(newSlide.find('.ccm-pick-slide-image'));
			attachFileManagerLaunchIcon(newSlide.find('.ccm-pick-slide-icon'));
			newSlide.find('div[data-field=entry-link-page-selector-select]').concretePageSelector({
				'inputName': 'internalLinkCID[]'
			});
			doSortCount();
		});

		$('.ccm-image-slider-entries').on('click','.ccm-edit-slide', function() {
			$(this).closest('.ccm-image-slider-entry').toggleClass('slide-closed');
			var thisEditButton = $(this).closest('.ccm-image-slider-entry').find('.btn.ccm-edit-slide');
			if (thisEditButton.data('slideEditText') === thisEditButton.text()) {
				thisEditButton.text(thisEditButton.data('slideCloseText'));
			} else if (thisEditButton.data('slideCloseText') === thisEditButton.text()) {
				thisEditButton.text(thisEditButton.data('slideEditText'));
			}
		});

		$('.ccm-image-slider-entries').sortable({
			placeholder: "ui-state-highlight",
			axis: "y",
			handle: "i.fa-arrows<?= $v9 ? '-alt' : ''?>",
			cursor: "move",
			update: function() {
				doSortCount();
			}
		});

		attachDelete($('.ccm-delete-image-slider-entry'));
		attachFileManagerLaunch($('.ccm-pick-slide-image'));
		attachFileManagerLaunchIcon($('.ccm-pick-slide-icon'));

		<?php
		if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) { ?>
		$(function () {  // activate editors
			if ($('.editor-content-<?php echo $bID; ?>').length) {
				launchEditor($('.editor-content-<?php echo $bID; ?>'));
			}
		});
		<?php
		} else {
		?>
		$(function () {  // activate redactors
			$('.redactor-content').redactor({
				minHeight: 200,
				'concrete5': {
					filemanager: <?php echo $fp->canAccessFileManager(); ?>,
					sitemap: <?php echo $tp->canAccessSitemap(); ?>,
					lightbox: true
				}
			});
		});
		<?php } ?>
	});
</script>
<style>
	.ccm-image-slider-block-container .redactor_editor {
		padding: 20px;
	}
	.ccm-image-slider-block-container input[type="text"],
	.ccm-image-slider-block-container textarea {
		display: block;
		width: 100%;
	}
	.ccm-image-slider-block-container .btn-success {
		margin-bottom: 20px;
	}
	.ccm-image-slider-entries {
		padding-bottom: 30px;
	}
	.ccm-image-slider-block-container .slide-well {
		min-height: 20px;
		padding: 10px;
		margin-bottom: 10px;
		background-color: #f5f5f5;
		border: 1px solid #e3e3e3;
		border-radius: 4px;
		-moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
		-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
		box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
	}
	.ccm-pick-slide-image,
	.ccm-pick-slide-icon {
		padding: 5px;
		cursor: pointer;
		background: #dedede;
		border: 1px solid #cdcdcd;
		text-align: center;
		vertical-align: middle;
		width: 72px;
		height: 72px;
		display: table-cell;
	}
	.ccm-pick-slide-image img,
	.ccm-pick-slide-icon img {
		max-width: 100%;
	}
	.ccm-image-slider-entry {
		position: relative;
		min-height:50px;
	}
	.ccm-image-slider-entry.slide-closed .form-group {
		display: none;
	}
	.ccm-image-slider-entry.slide-closed .form-group:first-of-type {
		display: block;
		margin-bottom: 0px;
	}
	.ccm-image-slider-entry.slide-closed .form-group:first-of-type label {
		display: none;
	}

	.ccm-image-slider-block-container i:hover {
		color: #428bca;
	}
	.ccm-image-slider-block-container i.fa-arrows<?= $v9 ? '-alt' : ''?> {
		position: absolute;
		top: 6px;
		right: 5px;
		cursor: move;
		font-size: 20px;
		padding: 5px;
	}
	.ccm-image-slider-block-container .ui-state-highlight {
		height: 94px;
		margin-bottom: 15px;
	}
	.ccm-image-slider-block-container .show-slide-link {
		display: block;
	}
	.ccm-image-slider-block-container .hide-slide-link {
		display: none;
	}
</style>

<?= $v9 ? '<div class="tab-content">' : ''?>

<div class="<?= $v9 ? 'tab-pane show active' : 'ccm-tab-content'?>" id="<?= $v9 ? 'slides-'.$id : 'ccm-tab-content-slides' ?>" role="tabpanel">
	<div class="ccm-image-slider-block-container">
		<button type="button" class="btn btn-success ccm-add-image-slider-entry"><?php echo t('Add Slide'); ?></button>
		<div class="ccm-image-slider-entries">

		</div>
	</div>
</div>

<div class="<?= $v9 ? 'tab-pane' : 'ccm-tab-content'?>" id="<?= $v9 ? 'options-'.$id : 'ccm-tab-content-options' ?>" role="tabpanel">
	<div style="width:50%;float:left">
		<label class="control-label"><?php echo t('Navigation'); ?></label>
		<div class="form-group">
			<div class="radio">
				<label><input type="radio" name="<?php echo $view->field('navigationType'); ?>" value="0" <?php echo $navigationType == '0' ? 'checked' : ''; ?> /><?php echo t('None'); ?></label>
			</div>
		</div>
		<div class="form-group">
			<div class="radio">
				<label><input type="radio" name="<?php echo $view->field('navigationType'); ?>" value="1" <?php echo $navigationType == '1' ? 'checked' : ''; ?> /><?php echo t('Arrows'); ?></label>
			</div>
		</div>
		<div class="form-group">
			<div class="radio">
				<label><input type="radio" name="<?php echo $view->field('navigationType'); ?>" value="2" <?php echo $navigationType == '2' ? 'checked' : ''; ?> /><?php echo t('Bullets'); ?></label>
			</div>
		</div>

		<div class="form-group">
			<?php echo $form->label('timeout', t('Slide Duration')); ?>
			<div class="input-group" style="width: 150px">
				<?php echo $form->number('timeout', $timeout ? $timeout : 4000, array('min' => '1', 'max' => '99999'))?><span class="input-group-addon"><?php echo t('ms'); ?></span>
			</div>
		</div>
		<div class="form-group">
			<?php echo $form->label('speed', t('Slide Transition Speed')); ?>
			<div class="input-group" style="width: 150px">
				<?php echo $form->number('speed', $speed ? $speed : 500, array('min' => '1', 'max' => '99999'))?><span class="input-group-addon"><?php echo t('ms'); ?></span>
			</div>
		</div>
		<div class="form-group">
			<?php echo $form->label('speed', t('Transition Effect'));?>
			<div class="input-group" style="width: 150px">
				<?php echo $form->select('effect', $effects, $effect)?>
			</div>
		</div>
		<div class="form-group">
			<?php echo $form->label('maxZ', t('Maximum z-index')); ?>
			<div class="input-group" style="width: 150px">
				<?php echo $form->number('maxZ', $maxZ ? $maxZ : 100, array('min' => '20', 'max' => '10000'))?>
			</div>
			<p style="font-size: smaller"><?php echo t('Enter the maximum z-index value the slides will have. If you find you dropdown menu hides behind the slideshow, consider lowering this value')?>.</p>
		</div>
	</div>

	<div style="width:50%;float:right">
		<div class="form-group">
			<?php echo $form->label('sync', t('Sync transitions')); ?>
			<?php echo $form->checkbox('sync', $sync, $sync ? 'checked' : ''); ?>
			<p style="font-size: smaller"><?php echo t('If checked then animation of the incoming and outgoing slides will be synchronized.
			If unchecked then the animation for the incoming slide will not start until the animation for the outgoing slide completes')?>.</p>
		</div>
		<div class="form-group">
			<?php echo $form->label('swipe', t('Enable swipe for mobile devices')); ?>
			<?php echo $form->checkbox('swipe', $swipe, $swipe ? 'checked' : ''); ?>
			<p style="font-size: smaller"><?php echo t('If checked then mobile devices can swipe to move between slides')?>.</p>
		</div>
		<div class="form-group">
			<?php echo $form->label('noAnimate', t('Disable Autoplay')); ?>
			<?php echo $form->checkbox('noAnimate', $noAnimate, $noAnimate ? 'checked' : ''); ?>
		</div>
		<div class="form-group">
			<?php echo $form->label('fadeCaption', t('Fade captions')); ?>
			<?php echo $form->checkbox('fadeCaption', $fadeCaption, $fadeCaption ? 'checked' : ''); ?>
			<p style="font-size: smaller"><?php echo t('If checked then captions will fade in and out on each slide.')?>.</p>
		</div>
		<div class="form-group">
			<?php echo $form->label('pause', t('Pause Slideshow on Hover')); ?>
			<?php echo $form->checkbox('pause', $pause, $pause ? 'checked' : ''); ?>
		</div>
		<div class="form-group">
			<?php echo $form->label('buttonCSS', t('Button CSS classes')); ?>
			<?php echo $form->text('buttonCSS', ($buttonCSS ? $buttonCSS : 'btn btn-default')); ?>
			<p style="font-size: smaller"><?php echo t('Enter the CSS class name(s) to use or leave empty for the default btn btn-default values')?>.</p>
		</div>
	</div>
</div>

<div class="<?= $v9 ? 'tab-pane' : 'ccm-tab-content'?>" id="<?= $v9 ? 'about-'.$id : 'ccm-tab-content-about' ?>" role="tabpanel">
	<div class="ccm-image-slider-block-container">
		<label class="control-label"><?php echo t('About')?> Cycle2</label>
		<p><?php echo t('This addon makes use of the amazing')?> <a target="_blank" href="http://malsup.com/jquery/cycle2/">Cycle2 jQuery plugin</a>. <?php echo t('If you find it awesome, please visit
			the')?> <a target="_blank" href="http://malsup.com/jquery/cycle2/"><?php echo t('Cycle2 website')?></a> <?php echo t('and make a donation to the plugin author')?>. <a target="_blank" href="https://twitter.com/malsup">@malsup</a> <?php echo t('rocks')?>.</p>
	</div>
</div>

<?= $v9 ? '</div>' : ''?>

<script type="text/template" id="imageTemplate">
	<div class="ccm-image-slider-entry slide-well slide-closed">
		<div>
			<button type="button" class="btn btn-default btn-secondary ccm-edit-slide" data-slide-close-text="<?php echo t('Collapse Slide'); ?>" data-slide-edit-text="<?php echo t('Edit Slide'); ?>"><?php echo t('Edit Slide'); ?></button>
			<button type="button" class="btn btn-danger ccm-delete-image-slider-entry"><?php echo t('Remove'); ?></button>
			<i class="fa<?= $v9 ? 's' : ''?> fa-arrows<?= $v9 ? '-alt' : ''?>"></i>
		</div>
		<div class="form-group">
			<div style="width:50%;float:left">
				<label><?php echo t('Image'); ?></label>

				<div class="ccm-pick-slide-image">
					<% if (image_url.length > 0) { %>
					<img src="<%= image_url %>"/>
					<span style="font-size: 10px"><%= image_dimensions %></span>
					<% } else { %>
					<i class="fa fa-picture-o"></i>
					<% } %>
				</div>
				<input type="hidden" name="<?php echo $view->field('fID'); ?>[]" class="image-fID" value="<%=fID%>"/>
				<span style="font-size: 12px"><%= image_title %></span>
			</div>
			<div style="width:50%;float:left">
				<label><?php echo t('Icon (optional)'); ?></label>

				<div class="ccm-pick-slide-icon">
					<% if (icon_url.length > 0) { %>
					<img src="<%= icon_url %>"/>
					<span style="font-size: 10px"><%= icon_dimensions %></span>
					<% } else { %>
					<i class="fa fa-picture-o"></i>
					<% } %>
				</div>
				<input type="hidden" name="<?php echo $view->field('iconfID'); ?>[]" class="icon-fID" value="<%=iconfID%>"/>
				<span style="font-size: 12px"><%= icon_title %></span>
			</div>
		</div>
		<div class="form-group" style="clear: left">
			<label><?php echo t('Title'); ?></label>
			<input type="text" name="<?php echo $view->field('title'); ?>[]" value="<%=title%>" />
		</div>
		<div class="form-group" >
			<label><?php echo t('Description'); ?></label>
			<?php if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) { ?>
				<div class="editor-edit-content"></div>
				<textarea id="ccm-slide-editor-<%= _.uniqueId() %>" style="display: none" class="editor-content editor-content-<?php echo $bID; ?>"
						  name="<?php echo $view->field('description'); ?>[]"><%=description%></textarea>
			<?php } else { ?>}
				<div class="redactor-edit-content"></div>
				<textarea style="display: none" class="redactor-content" name="<?php echo $view->field('description'); ?>[]"><%=description%></textarea>
			<?php } ?>
		</div>
		<div class="form-group" >
			<label><?php echo t('Link'); ?></label>
			<select data-field="entry-link-select" name="linkType[]" class="form-control" style="width: 60%;">
				<option value="0" <% if (!link_type) { %>selected<% } %>><?php echo t('None'); ?></option>
				<option value="1" <% if (link_type == 1) { %>selected<% } %>><?php echo t('Another Page'); ?></option>
				<option value="2" <% if (link_type == 2) { %>selected<% } %>><?php echo t('External URL'); ?></option>
			</select>
		</div>
		<div data-field="entry-link-url" class="form-group hide-slide-link">
			<label><?php echo t('URL:'); ?></label>
			<textarea name="linkURL[]"><%=link_url%></textarea>
		</div>
		<div data-field="entry-link-page-selector" class="form-group hide-slide-link">
			<label><?php echo t('Choose Page:'); ?></label>
			<div data-field="entry-link-page-selector-select"></div>
		</div>
		<div class="form-group" >
			<label><?php echo t('Button text'); ?></label>
			<input type="text" name="<?php echo $view->field('buttonText'); ?>[]" value="<%=buttonText%>" />
		</div>
		<input class="ccm-image-slider-entry-sort" type="hidden" name="<?php echo $view->field('sortOrder'); ?>[]" value="<%=sort_order%>"/>
	</div>
</script>