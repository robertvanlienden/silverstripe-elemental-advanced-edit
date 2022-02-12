<?php

namespace RobertVanLienden\ElementalAdvancedEdit\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Control\Controller;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Forms\FieldList;
use SilverStripe\CMS\Model\SiteTree;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\Config\Config;

/***
 * Class BaseElementExtension
 * @package RobertVanLienden\ElementalAdvancedEdit\Extensions;
 *
 * @property BaseElement $owner
 */
class BaseElementExtension extends DataExtension
{
    /**
     * Set to false to prevent an in-line edit form from showing in an elemental area. Instead the element will be
     * clickable and a GridFieldDetailForm will be used.
     *
     * @config
     * @var bool
     */
    private static $inline_editable = true;

    /**
     * Set to true to add an link to the GridFieldDetailForm.
     *
     * @config
     * @var bool
     */
    private static $advanced_editing = true;

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;

        if ($owner->inlineEditable() === true &&
            $this->advancedEditing() === true &&
            !strpos($_SERVER['REQUEST_URI'], 'EditForm')
        )
        {
            // Check for title settings field to support thewebmen\silverstripe-elemental-grid
            if (class_exists(\TheWebmen\ElementalGrid\Extensions\BaseElementExtension::class)) {
                $fields->insertBefore('TitleSettings',
                    LiteralField::create('AdvancedEditLink',
                        sprintf('<strong>
        <a href="%s" target="_self">
            Advanced edit
        </a>
        </strong>', $owner->getEditLink())
                    ),
                );
            } else { // Default field to insert before
                $fields->insertBefore('Title',
                    LiteralField::create('AdvancedEditLink',
                        sprintf('<strong>
        <a href="%s" target="_self">
            Advanced edit
        </a>
        </strong>', $owner->getEditLink())
                    ),
                );
            }

        }
    }

    public function updateCMSEditLink(&$link)
    {
        /** @var BaseElement $owner */
        $owner = $this->owner;

        if ($this->advancedEditing() && $owner->ID) {
            /** @var \Page $page */
            $page = $owner->getPage();
            $relationName = $owner->getAreaRelationName();

            $link = Controller::join_links(
                singleton(CMSPageEditController::class)->Link('EditForm'),
                $page->ID,
                'field/' . $relationName . '/item/',
                $owner->ID,
                'edit'
            );
        }
    }

    /**
     * Proxy through to configuration setting 'advanced_editing'
     *
     * @return bool
     */
    public function advancedEditing() {
        return $this->owner->config()->get('advanced_editing');
    }
}
