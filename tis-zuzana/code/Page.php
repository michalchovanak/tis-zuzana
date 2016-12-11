<?php

class Page extends Cleopas_Pages_Base
{
    private static $can_be_root = true;

    protected function getGridFieldConfig()
    {
        return GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            (new GridFieldAddNewButton('toolbar-header-right')),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldPaginator(50),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm(),
            new GridFieldOrderableRows()
        );
    }
}

class Page_Controller extends Cleopas_Controllers_Base
{
    protected $is_data_cached = false;

    public function setupWidgets()
    {
        $this->addWidget('Meta', Cleopas\Widgets\Meta\Basic::create()
            ->setWebsiteTitle(function() {
                return $this->getSiteConfig()->getTitle();
            })
            ->setPageTitle(function() {
                return $this->Title;
            }));

        $this->addWidget('Menu', Cleopas\Widgets\Menu\Menu::create()
            ->setItems(function() {
                return MenuItem::get()->sort('SortOrder');
            }));

        $this->addWidget('SocialNetworks', Cleopas\Widgets\SocialNetworks\Basic::create()
            ->setItems(function() {
                return $this->getSiteConfig()->SocialNetworks()->Sort('Sort ASC');
            }));

        $this->addWidget('Copyright', Cleopas\Widgets\Copyright\Basic::create()
            ->setOwnerName('Transparency International SK')
            ->setFromYear(2016));
    }

    protected function setupForms()
    {
        $this->addFormWidget('Supporter');
    }
}
