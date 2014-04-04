<?php
/**
 * PHP Server Monitor
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     phpservermon
 * @author      Pepijn Over <pep@neanderthal-technology.com>
 * @copyright   Copyright (c) 2008-2014 Pepijn Over <pep@neanderthal-technology.com>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: @package_version@
 * @link        http://www.phpservermonitor.org/
 **/

namespace psm\Util\Module;
use psm\Service\Template;

class Sidebar implements SidebarInterface {

	/**
	 * ID of active item
	 * @var string $active_id
	 * @see setActiveItem()
	 */
	protected $active_id;

	/**
	 * List of all sidebar items
	 * @var array $items
	 */
	protected $items = array();

	/**
	 * Custom subtitle
	 * @var string $subtitle
	 * @see setSubtitle()
	 */
	protected $subtitle;

	/**
	 * Template service
	 * @var \psm\Service\Template $tpl
	 */
	protected $tpl;

	public function __construct(Template $tpl) {
		$this->tpl = $tpl;
	}

	/**
	 * Set active item
	 * @param string $id
	 * @return \psm\Util\Module\Sidebar
	 */
	public function setActiveItem($id) {
		$this->active_id = $id;
		return $this;
	}

	/**
	 * Set a custom subtitle (default is module subitle)
	 * @param string $title
	 * @return \psm\Util\Moduke\Sidebar
	 */
	public function setSubtitle($title) {
		$this->subtitle = $title;
		return $this;
	}

	/**
	 * Add new link to sidebar
	 * @param string $id
	 * @param string $label
	 * @param string $url
	 * @param string $icon
	 * @return \psm\Util\Module\Sidebar
	 */
	public function addLink($id, $label, $url, $icon = null) {
		if(!isset($this->items['link'])) {
			$this->items['link'] = array();
		}

		$this->items['link'][$id] = array(
			'id' => $id,
			'label' => $label,
			'url' => str_replace('"', '\"', $url),
			'icon' => $icon,
		);
		return $this;
	}

	/**
	 * Add a new button to the sidebar
	 * @param string $id
	 * @param string $label
	 * @param string $url
	 * @param string $icon
	 * @param string $btn_class
	 * @param boolean $url_is_onclick if you want onclick rather than url, change this to true
	 * @return \psm\Util\Module\Sidebar
	 */
	public function addButton($id, $label, $url, $icon = null, $btn_class = null, $url_is_onclick = false) {
		if(!isset($this->items['button'])) {
			$this->items['button'] = array();
		}
		if(!$url_is_onclick) {
			$url = "psm_goTo('" . $url . "');";
		}

		$this->items['button'][$id] = array(
			'id' => $id,
			'label' => $label,
			'onclick' => str_replace('"', '\"', $url),
			'icon' => $icon,
			'btn_class'=> $btn_class,
		);
		return $this;
	}

	/**
	 * Add dropdown button
	 * @param string $id
	 * @param string $label
	 * @param array $options
	 * @param string $icon
	 * @param string $btn_class
	 * @return \psm\Util\Module\Sidebar
	 */
	public function addDropdown($id, $label, $options, $icon = null, $btn_class = null) {
		if(!isset($this->items['dropdown'])) {
			$this->items['dropdown'] = array();
		}
		$this->items['dropdown'][$id] = array(
			'id' => $id,
			'label' => $label,
			'options' => $options,
			'icon' => $icon,
			'btn_class' => $btn_class,
		);
		return $this;
	}

	public function createHTML() {
		$tpl_id = 'main_sidebar_container';
		$this->tpl->newTemplate($tpl_id, 'main_sidebar.tpl.html');

		$types = array('dropdown', 'button', 'link');
		$items = array();

		// loop through all types and build their html
		foreach($types as $type) {
			if(empty($this->items[$type])) {
				// no items for this type
				continue;
			}
			// retrieve template for this type once so we can use it in the loop
			$tpl_id_type = 'main_sidebar_types_' . $type;
			$this->tpl->newTemplate($tpl_id_type, 'main_sidebar.tpl.html');
			$html_type = $this->tpl->getTemplate($tpl_id_type);

			// build html for each individual item
			foreach($this->items[$type] as $id => $item) {
				$html_item = $html_type;

				if(isset($item['options'])) {
					$item['options'] = $this->tpl->addTemplateDataRepeat($html_item, 'options', $item['options'], true);

				}
				$html_item = $this->tpl->addTemplateData($html_type, $item, true);

				$items[] = array(
					'html_item' => $html_item,
					'class_active' => ($id === $this->active_id) ? 'active' : '',
				);
			}
		}
		if(!empty($items)) {
			$this->tpl->addTemplateDataRepeat($tpl_id, 'items', $items);
		}
		if($this->subtitle !== null) {
			$this->tpl->addTemplateData($tpl_id, array(
				'subtitle' => $this->subtitle,
			));
		}

		$html = $this->tpl->getTemplate($tpl_id);

		return $html;
	}
}