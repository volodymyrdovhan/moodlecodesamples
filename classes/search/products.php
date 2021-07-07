<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Search area for block_social members
 *
 * @package    block_social
 * @copyright  2018 SEBALE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_intellicart\search;

use core_search\moodle_recordset;
use local_intellicart\product;

defined('MOODLE_INTERNAL') || die();

/**
 * Search area for block_social members
 *
 * @package    block_social
 * @copyright  2018 SEBALE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class products extends \core_search\base_block {

    protected $available_products = array();

    /**
     * The context levels the search implementation is working on.
     *
     * @var array
     */
    protected static $levels = [CONTEXT_SYSTEM];

    /**
     * Returns recordset containing required data for indexing groups.
     *
     * @param int $modifiedfrom timestamp
     * @return \moodle_recordset
     */
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        global $DB;

        return $DB->get_recordset_sql(
            "SELECT p.*  
               FROM {local_intellicart_products} p 
              WHERE p.timemodified >= ? 
           ORDER BY p.timemodified ASC",
            array('timemodified' => $modifiedfrom)
        );
    }

    /**
     * Returns the document associated with this user.
     *
     * @param stdClass $record
     * @param array    $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) {
        global $CFG, $DB;

        $systemcontext = \context_system::instance();

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($record->name, false));
        $doc->set('content', content_to_text(product::get_product_info($record), FORMAT_HTML));
        $doc->set('contextid', $systemcontext->id);
        $doc->set('courseid', SITEID);
        $doc->set('modified', $record->timemodified);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && $options['lastindexedtime'] < $record->timecreated) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }

        return $doc;
    }

    public function uses_file_indexing() {
        return true;
    }

    public function attach_files($document) {
        $fs = get_file_storage();
        $context = \context_module::instance($document->get('contextid'));

        $files = $fs->get_area_files(\context_system::instance()->id, 'local_intellicart', 'productimage', $document->itemid);
        foreach ($files as $file) {
            $document->add_stored_file($file);
        }
    }

    /**
     * Whether the user can access the document or not.
     *
     * @param int $id The course instance id.
     * @return int
     */
    public function check_access($id) {
        global $DB, $USER;

        $systemcontext = \context_system::instance();

        $product = $DB->get_record('local_intellicart_products', array('id' => $id));
        if (!$product) {
            return \core_search\manager::ACCESS_DELETED;
        }
        if ($product->visible != 1) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (!count($this->available_products)) {
            $this->set_available_products();
        }
        if (!in_array($product->id, $this->available_products)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    public function set_available_products() {
        $available_products = product::get_available_products(0, 0, 0, ['allproducts' => 1], true);
        $this->available_products = (count($available_products['products'])) ? array_keys($available_products['products']) : array('0');
    }

    /**
     * Link to the user.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) {
        return new \moodle_url('/local/intellicart/view.php', array('id' => $doc->get('itemid')));
    }
    
}
