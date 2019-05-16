<?php

// Enables the Writing Helper plugin that is a part of WordPress.com but not Jetpack.
if ( true === apply_filters( 'wpcom_compat_enable_writing_helper', true ) && ! class_exists( 'Writing_Helper' ) ) {
	require __DIR__ . '/plugins/writing-helper/writing-helper.php';
}

