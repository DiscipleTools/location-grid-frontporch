<div class="grid-x grid-padding-x" style="padding-bottom: 0;">
    <div class="cell center">
        <ul>
            <li style="display: inline;"><a href="/">Home</a></li> |
            <?php if( is_user_logged_in() ) : ?>
                <li style="display: inline;"><a href="/grid_app/profile">Profile</a></li> |
                <li style="display: inline;"><a href="/login/?action=logout">Logout</a></li>
            <?php else: ?>
                <li style="display: inline;"><a href="/login">Login</a></li>
            <?php endif; ?>
        </ul>
        <hr>
    </div>
</div>

