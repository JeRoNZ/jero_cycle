<?php 
namespace Concrete\Package\JeroCycle;

use BlockType;
use Package;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Asset\Asset as Asset;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package {
	protected $pkgHandle = 'jero_cycle';
	protected $appVersionRequired = '5.7.5';
	protected $pkgVersion = '1.1.3';

	public function getPackageName () {
		return t('Cycle2 Slide Show');
	}

	public function getPackageDescription () {
		return t('A mobile friendly responsive image slider using the amazing Cycle2 plugin');
	}

	public function install () {
		$pkg = parent::install();
		BlockType::installBlockType('jero_cycle',$pkg);
	}

	public function on_start(){
		// because I want to be selective with which files are included, they are in the assets folder rather than js
		// which otherwise would push everything into the page footer.
		$al = AssetList::getInstance();
		$al->register('javascript', 'cycle2', '/assets/jquery.cycle2.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => '2.1.6', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2caption','assets/jquery.cycle2.caption2.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2swipe', 'assets/jquery.cycle2.swipe.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2flip', 'assets/jquery.cycle2.flip.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2scrollVert', 'assets/jquery.cycle2.scrollVert.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2tile', 'assets/jquery.cycle2.tile.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2shuffle', 'assets/jquery.cycle2.shuffle.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
		$al->register('javascript', 'cycle2carousel', 'assets/jquery.cycle2.carousel.min.js', array('position' => Asset::ASSET_POSITION_HEADER, 'version' => 'v20141007', 'minify' => false, 'combine' => true),$this->pkgHandle);
	}
}
