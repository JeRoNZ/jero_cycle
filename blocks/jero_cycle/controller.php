<?php  namespace Concrete\Package\JeroCycle\Block\JeroCycle;

defined("C5_EXECUTE") or die("Access Denied.");

use Concrete\Core\Block\BlockController;
use Concrete\Core\Editor\LinkAbstractor;
use Core;
use File;
use Database;
use Page;

class Controller extends BlockController {
	public $helpers = array(
		0 => 'form',
	);
	public $btFieldsRequired = array();
	protected $btExportFileColumns = array(
		0 => 'image',
	);
	protected $btTable = 'btJeroCycle';
	protected $btInterfaceWidth = 600;
	protected $btInterfaceHeight = 600;
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btDefaultSet = 'multimedia';

	protected $effectsList =
		array(
			'fade' => 'fade',
			'fadeout' => 'fadeout',
			'scrollHorz' => 'scrollHorz',
			'scrollVert' => 'scrollVert',
			'shuffle' => 'shuffle',
			'continuous' => 'continuous',
			'flipHorz' => 'flipHorz',
			'flipVert' => 'flipVert',
			'tileSlide' => 'tileSlide',
			'tileBlind' => 'tileBlind'
		);

	public function getBlockTypeDescription () {
		return t("Yet another image slide show, this one uses the amazing responsive cycle2 plugin");
	}

	public function getBlockTypeName () {
		return t("Cycle2 Slide Show");
	}

	public function getSearchableContent () {
		$content = '';
		$db = Database::connection();
		$v = array($this->bID);
		$q = 'SELECT * FROM btJeroCycleEntries WHERE bID = ?';
		$r = $db->query($q, $v);
		foreach ($r as $row) {
			$content .= $row['title'] . ' ';
			$content .= $row['description'] . ' ';
		}

		return $content;
	}

	public function view () {
		$uh = Core::make('helper/concrete/urls');
		$bObj = $this->getBlockObject();
		$bt = $bObj->getBlockTypeObject();
		$blockURL = $uh->getBlockTypeAssetsURL($bt);
		$this->set("blockURL", $blockURL); // Required for next/previous arrows

		$this->requireAsset('javascript', 'jquery');
		$this->requireAsset('javascript', 'cycle2');
		$sets = $this->getSets();
		if ($sets['swipe'] == 1) {
			$this->requireAsset('javascript', 'cycle2swipe');
		}
		if ($sets['fadeCaption'] == 1) {
			$this->requireAsset('javascript', 'cycle2caption');
		}
		switch ($sets['effect']) {
			case 'scrollVert':
				$this->requireAsset('javascript', 'cycle2scrollVert');
				break;
			case 'shuffle':
				$this->requireAsset('javascript', 'cycle2shuffle');
				break;
			case 'flipHorz':
			case 'flipVert':
				$this->requireAsset('javascript', 'cycle2flip');
				break;
			case 'tileSlide':
			case 'tileBlind':
				$this->requireAsset('javascript', 'cycle2tile');
				break;
		}

		$this->set('rows', $this->getEntries());

	}

	public function add () {
		$this->requireAsset('core/file-manager');
		$this->requireAsset('core/sitemap');
		$this->requireAsset('redactor');
		$this->set('effects', $this->effectsList);
	}

	public function getEntries () {
		$db = Database::connection();
		$r = $db->fetchAll('SELECT * FROM btJeroCycleEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
		// in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
		$rows = array();

		$ratio = false;
		foreach ($r as $q) {
			if (!$q['linkURL'] && $q['internalLinkCID']) {
				$c = Page::getByID($q['internalLinkCID'], 'ACTIVE');
				$q['linkURL'] = $c->getCollectionLink();
				$q['linkPage'] = $c;
			}
			$q['description'] = LinkAbstractor::translateFrom($q['description']);

			$fo = File::getByID($q['fID']);
			if (! $fo) {
				continue;
			}
			$fv = $fo->getVersion();
			if (!$ratio) {
				$ratio = $fv->getAttribute('width') . ':' . $fv->getAttribute('height');
			}
			$q['fV'] = $fv;
			$rows[] = $q;
		}
		$this->set('ratio', $ratio);

		return $rows;
	}

	public function duplicate ($newBID) {
		parent::duplicate($newBID);
		$db = Database::connection();
		$v = array($this->bID);
		$q = 'SELECT * FROM btJeroCycleEntries WHERE bID = ?';
		$r = $db->query($q, $v);
		while ($row = $r->FetchRow()) {
			$db->executeQuery('INSERT INTO btJeroCycleEntries (bID, fID, linkURL, title, description, sortOrder, internalLinkCID, buttonText) values(?,?,?,?,?,?,?,?)',
				array(
					$newBID,
					$row['fID'],
					$row['linkURL'],
					$row['title'],
					$row['description'],
					$row['sortOrder'],
					$row['internalLinkCID'],
					$row['buttonText']
				)
			);
		}
	}

	public function delete () {
		$db = Database::connection();
		$db->delete('btImageSliderEntries', array('bID' => $this->bID));
		parent::delete();
	}

	public function edit () {
		$this->requireAsset('core/file-manager');
		$this->requireAsset('core/sitemap');
		$this->requireAsset('redactor');
		$db = Database::connection();
		$query = $db->FetchAll('SELECT * from btJeroCycleEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
		$this->set('rows', $query);
		$this->set('effects', $this->effectsList);
	}


	public function save ($args) {
		$args += array(
			'timeout' => 4000,
			'speed' => 500,
		);
		$args['timeout'] = intval($args['timeout']);
		$args['speed'] = intval($args['speed']);
		$args['maxZ'] = intval($args['maxZ']) < 20 ? 100 : intval($args['maxZ']);
		$args['navigationType'] = intval($args['navigationType']);
		$args['effect'] = in_array($args['effect'], $this->effectsList) ? $args['effect'] : 'fade';
		$args['sync'] = isset($args['sync']) ? 1 : 0;
		$args['noAnimate'] = isset($args['noAnimate']) ? 1 : 0;
		$args['pause'] = isset($args['pause']) ? 1 : 0;
		$args['fadeCaption'] = isset($args['fadeCaption']) ? 1 : 0;
		$args['swipe'] = isset($args['swipe']) ? 1 : 0;
		$args['buttonCSS'] = $args['buttonCSS'] ? trim($args['buttonCSS'])  : 'btn btn-default';

		$db = Database::connection();
		$db->executeQuery('DELETE from btJeroCycleEntries WHERE bID = ?', array($this->bID));
		parent::save($args);
		if (isset($args['sortOrder'])) {
			$count = count($args['sortOrder']);
			$i = 0;

			while ($i < $count) {
				$linkURL = $args['linkURL'][$i];
				$internalLinkCID = $args['internalLinkCID'][$i];
				switch (intval($args['linkType'][$i])) {
					case 1:
						$linkURL = '';
						break;
					case 2:
						$internalLinkCID = 0;
						break;
					default:
						$linkURL = '';
						$internalLinkCID = 0;
						break;
				}

				if (isset($args['description'][$i])) {
					$args['description'][$i] = LinkAbstractor::translateTo($args['description'][$i]);
				}

				$db->executeQuery('INSERT INTO btJeroCycleEntries (bID, fID, linkURL, internalLinkCID, title, description, buttonText, sortOrder) VALUES(?,?,?,?,?,?,?,?)',
					array(
						$this->bID,
						intval($args['fID'][$i]),
						$linkURL,
						$internalLinkCID,
						$args['title'][$i],
						$args['description'][$i],
						$args['buttonText'][$i],
						$args['sortOrder'][$i]
					)
				);
				++$i;
			}
		}
	}

	public function composer () {
		$this->edit();
	}
}
