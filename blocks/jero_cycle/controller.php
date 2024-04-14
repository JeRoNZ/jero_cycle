<?php  namespace Concrete\Package\JeroCycle\Block\JeroCycle;

defined("C5_EXECUTE") or die("Access Denied.");

use Concrete\Core\Block\BlockController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\Support\Facade\Application;
use Core;
use File;
use Database;
use Page;


class Controller extends BlockController implements FileTrackableInterface {
	public $helpers = [
		0 => 'form'
	];
	public $btFieldsRequired = [];
	protected $btExportFileColumns = [
		0 => 'image',
	];
	protected $btTable = 'btJeroCycle';
	protected $btInterfaceWidth = 600;
	protected $btInterfaceHeight = 600;
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btDefaultSet = 'multimedia';

	protected $effectsList =
		[
			'fade' => 'fade',
			'fadeout' => 'fadeout',
			'scrollHorz' => 'scrollHorz',
			'scrollVert' => 'scrollVert',
			'shuffle' => 'shuffle',
			'continuous' => 'continuous',
			'flipHorz' => 'flipHorz',
			'flipVert' => 'flipVert',
			'tileSlide' => 'tileSlide',
			'tileBlind' => 'tileBlind',
			'carousel' => 'carousel',
		];

	public function getBlockTypeDescription () {
		return t('Yet another image slide show, this one uses the amazing responsive cycle2 plugin');
	}

	public function getBlockTypeName () {
		return t('Cycle2 Slide Show');
	}

	public function getSearchableContent () {
		$content = '';
		$rows = $this->_getEntries();
		foreach ($rows as $row) {
			$content .= $row['title'] . ' ';
			$content .= $row['description'] . ' ';
		}

		return $content;
	}

	public function on_start () {
		$uh = $this->app->make('helper/concrete/urls');
		$bObj = $this->getBlockObject();
		if (! $bObj) { // this null when adding a new block
			return;
		}
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
			case 'carousel':
				$this->requireAsset('javascript', 'cycle2carousel');
				break;
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
	}


	public function view () {
		$this->set('rows', $this->getEntries());
	}

	public function add () {
		$this->requireAsset('core/file-manager');
		$this->requireAsset('core/sitemap');
		if (version_compare(\Config::get('concrete.version'), '8.0', '<')) {
			$this->requireAsset('redactor');
		}
		$this->set('effects', $this->effectsList);

		// PHP 8+ fixes
		$this->set('navigationType', 0);
		$this->set('timeout', null);
		$this->set('speed', null);
		$this->set('speed', null);
		$this->set('effect', null);
		$this->set('maxZ', null);
		$this->set('sync', null);
		$this->set('swipe', null);
		$this->set('noAnimate', null);
		$this->set('fadeCaption', null);
		$this->set('pause', null);
		$this->set('buttonCSS', null);
		$this->set('bID', 42);
		$this->set('rows', []);
	}

	private function _getEntries() {
		$db = Database::connection();
		$r = $db->fetchAllAssociative('SELECT * FROM btJeroCycleEntries WHERE bID = ? ORDER BY sortOrder', [$this->bID]);

		return $r;
	}

	public function getEntries () {
		$r = $this->_getEntries();
		// in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
		$rows = [];

		$ratio = false;
		foreach ($r as &$q) {
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
			$fo = File::getByID($q['iconfID']);
			/* @var $fo \Concrete\Core\Entity\File\File */
			if (! $fo) {
				$q['fvIcon'] = null;
			} else {
				$fv = $fo->getVersion();
				/* @var $fv \Concrete\Core\Entity\File\Version */
				$q['fvIcon'] = $fv;
			}
			$rows[] = $q;
		}
		$this->set('ratio', $ratio);

		return $rows;
	}


	public function duplicate($newBID)
	{
		parent::duplicate($newBID);
		$db = $this->app->make(Connection::class);
		$copyFields = 'fID, iconfID, linkURL, title, description, sortOrder, internalLinkCID, buttonText';
		$db->executeStatement(
			"INSERT INTO btJeroCycleEntries (bID, {$copyFields}) SELECT ?, {$copyFields} FROM btJeroCycleEntries WHERE bID = ?",
			[
				$newBID,
				$this->bID
			]
		);
	}

	public function delete () {
		$db = Database::connection();
		$db->delete('btImageSliderEntries', ['bID' => $this->bID]);
		parent::delete();
	}

	public function edit () {
		$this->requireAsset('core/file-manager');
		$this->requireAsset('core/sitemap');
		if (version_compare(\Config::get('concrete.version'), '8.0', '<')) {
			$this->requireAsset('redactor');
		}
		$db = Database::connection();
		$query = $db->fetchAllAssociative('SELECT * from btJeroCycleEntries WHERE bID = ? ORDER BY sortOrder', array($this->bID));
		$this->set('rows', $query);
		$this->set('effects', $this->effectsList);
	}


	public function save ($args) {
		$args += [
			'timeout' => 4000,
			'speed' => 500
		];
		$args['timeout'] = (int) $args['timeout'];
		$args['speed'] = (int) ($args['speed']);
		$args['maxZ'] = (int) ($args['maxZ']) < 20 ? 100 : intval($args['maxZ']);
		$args['navigationType'] = isset($args['navigationType']) ?  (int) $args['navigationType'] : 0 ;
		$args['effect'] = in_array($args['effect'], $this->effectsList) ? $args['effect'] : 'fade';
		$args['sync'] = isset($args['sync']) ? 1 : 0;
		$args['noAnimate'] = isset($args['noAnimate']) ? 1 : 0;
		$args['pause'] = isset($args['pause']) ? 1 : 0;
		$args['fadeCaption'] = isset($args['fadeCaption']) ? 1 : 0;
		$args['swipe'] = isset($args['swipe']) ? 1 : 0;
		$args['buttonCSS'] = $args['buttonCSS'] ? trim($args['buttonCSS'])  : 'btn btn-default';

		$db = Database::connection();
		$db->executeQuery('DELETE from btJeroCycleEntries WHERE bID = ?', [$this->bID]);

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

				$db->executeQuery('INSERT INTO btJeroCycleEntries (bID, fID, iconfID, linkURL, internalLinkCID, title, description, buttonText, sortOrder) VALUES(?,?,?,?,?,?,?,?,?)',
					[
						$this->bID,
						(int) $args['fID'][$i],
						(int) $args['iconfID'][$i],
						$linkURL,
						$internalLinkCID,
						$args['title'][$i],
						$args['description'][$i],
						$args['buttonText'][$i],
						$args['sortOrder'][$i]
					]
				);
				++$i;
			}
		}

		parent::save($args);
	}

	public function composer () {
		$this->edit();
	}

	public function getUsedFiles()
	{
		$files = [];
		$rows = $this->_getEntries();
		foreach ($rows as $r){
			if ($r['fID'] > 0) {
				$files[] = $r['fID'];
			}
			if ($r['iconfID'] > 0){
				$files[] = $r['iconfID'];
			}
		}
		return $files;
	}
}
