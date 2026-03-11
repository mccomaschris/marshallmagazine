<?php

add_filter( 'mu_auth_super_users', function( $users ) {
    return array( 'cmccomas', 'bajus', 'madden24' );
} );
