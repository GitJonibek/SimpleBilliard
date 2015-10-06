<!-- START app/View/Elements/header_sp_feeds_alt.ctp -->
<div class="col sp-feed-alt height_40px col-xxs-12 <?= $is_mb_app ? "mp-app-feed-alt" : "hidden-md hidden-lg" ?>"
     id="SubHeaderMenu">
    <div class="col col-xxs-6 text-align_r">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px sp-feed-active"
           id="SubHeaderMenuFeed">
            <?= __d('gl', "ニュースフィード") ?>
        </a>
    </div>
    <div class="col col-xxs-6">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px"
           id="SubHeaderMenuGoal">
            <?= __d('gl', "関連ゴール") ?>
        </a>
    </div>
</div>
<!-- END app/View/Elements/header_sp_feeds_alt.ctp -->
