<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2014 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Bootstrap\Core\Contao\DataContainer;

use Netzmacht\Bootstrap\Core\Bootstrap;
use Input;
use LayoutModel;
use MetaPalettes;

/**
 * Class Layout
 * @package Netzmacht\Bootstrap\Core\Contao\DataContainer
 */
class Layout
{

	/**
	 * modify palette if bootstrap is used
	 *
	 * @hook palettes_hook (MetaPalettes)
	 */
	public function generatePalette()
	{
		// TODO: How to handle editAll actions?
		if(Input::get('table') != 'tl_layout' || Input::get('act') != 'edit') {
			return;
		}

		$layout = LayoutModel::findByPk(Input::get('id'));

		// dynamically render palette so that extensions can plug into default palette
		if($layout->layoutType == 'bootstrap') {
			$metaPalettes                             = & $GLOBALS['TL_DCA']['tl_layout']['metapalettes'];
			$metaPalettes['__base__']                 = $this->getMetaPaletteOfPalette('tl_layout');
			$metaPalettes['default extends __base__'] = Bootstrap::getConfigVar('layout.metapalette', array());

			// unset default palette. otherwise metapalettes will not render this palette
			unset($GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

			$subSelectPalettes = Bootstrap::getConfigVar('layout.metasubselectpalettes', array());

			foreach($subSelectPalettes as $field => $meta) {
				foreach($meta as $value => $definition) {
					unset($GLOBALS['TL_DCA']['tl_layout']['subpalettes'][$field . '_' . $value]);
					$GLOBALS['TL_DCA']['tl_layout']['metasubselectpalettes'][$field][$value] = $definition;
				}
			}
		}
		else {
			MetaPalettes::appendFields('tl_layout', 'title', array('layoutType'));
		}
	}


	/**
	 * Creates an meta palette of a palettes
	 *
	 * @param string $table
	 * @param string $name
	 * @return array
	 */
	protected function getMetaPaletteOfPalette($table, $name = 'default')
	{
		$palette     = $GLOBALS['TL_DCA'][$table]['palettes'][$name];
		$metaPalette = array();
		$legends     = explode(';', $palette);

		foreach($legends as $legend) {
			$fields = explode(',', $legend);

			preg_match('/\{(.*)_legend(:hide)?\}/', $fields[0], $matches);

			if(isset($matches[2])) {
				$fields[0] = $matches[2];
			} else {
				array_shift($fields);
			}

			$metaPalette[$matches[1]] = $fields;
		}

		return $metaPalette;
	}

} 