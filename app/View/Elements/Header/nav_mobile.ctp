<?= $this->App->viewStartComment() ?>
<div class="glHeaderMobile">
    <div class="glHeaderMobile-left">
        <?= $this->element('Header/back_btn'); ?>
    </div>
    <div class="glHeaderMobile-right">
        <ul class="glHeaderMobile-nav">
            <li class="glHeaderMobile-nav-menu">
                <a href="/search" class="glHeaderMobile-nav-menu-link">
                    <i class="material-icons">search</i>
                </a>
            </li>
            <li class="glHeaderMobile-nav-menu">
                <a id="GlHeaderMenuDropdown-Create" href="#" class="glHeaderMobile-nav-menu-link" data-toggle="dropdown">
                    <i class="material-icons">add_circle</i>
                </a>
                <ul class="dropdown-menu glHeader-nav-dropdown mod-mobile"
                    aria-labelledby="GlHeaderMenuDropdown-Create">
                    <?php if ($this->Session->read('current_team_id')): ?>
                        <li class="glHeader-nav-dropdown-menu">
                            <a class="glHeader-nav-dropdown-menu-link"
                               href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'create', 'step1']) ?>">
                                <div class="glHeader-nav-dropdown-menu-link-left">
                                    <i class="material-icons">flag</i>
                                </div>
                                <p class=""><?= __('Create a goal') ?></p>
                            </a>
                        </li>
                        <li class="glHeader-nav-dropdown-menu">
                            <a class="glHeader-nav-dropdown-menu-link" href="#" data-toggle="modal"
                               data-target="#modal_add_circle">
                                <div class="glHeader-nav-dropdown-menu-link-left">
                                    <i class="material-icons">group_work</i>
                                </div>
                                <p class=""><?= __('Create a circle') ?></p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="glHeader-nav-dropdown-menu">
                        <a class="glHeader-nav-dropdown-menu-link"
                           href="<?= $this->Html->url(['controller' => 'teams', 'action' => 'add']) ?>">
                            <div class="glHeader-nav-dropdown-menu-link-left">
                                <i class="material-icons">people</i>
                            </div>
                            <p class=""><?= __('Create a team') ?></p>
                        </a>
                    </li>
                </ul>

            </li>
        </ul>
    </div>
</div>
<?= $this->App->viewEndComment() ?>
