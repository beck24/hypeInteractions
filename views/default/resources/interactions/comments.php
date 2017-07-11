<?php

namespace hypeJunction\Interactions;

use ElggEntity;

$entity_guid = get_input('entity_guid', $vars);
$comment_guid = get_input('comment_guid', $vars);

$entity = get_entity($entity_guid);
/* @var $entity ElggEntity */

if (!elgg_instanceof($entity)) {
	return;
}

$comment = get_entity($comment_guid);
/* @var $comment Comment */

if (elgg_is_xhr()) {
	// List comments for ajax loading
	// We do not need the entire page shell
	echo elgg_view('framework/interactions/comments', array(
		'entity' => $entity,
		'comment' => $comment,
		'active_tab' => ($comment_guid) ? 'comments' : false,
		'show_add_form' => !get_input('sort') && !get_input('query') && !get_input('filter'),
	));
} else {
	$title = elgg_echo('interactions:comments:title', array($entity->getDisplayName()));

	if (elgg_is_active_plugin('hypeUI')) {
		// Show comments
		$responses = elgg_view_comments($entity, true, [
			'entity' => $entity,
			'comment' => $comment,
			'active_tab' => 'comments',
			'show_add_form' => true,
			'expand_form' => true,
		]);

		// Show partial entity listing
		$content = elgg_view_entity($entity, [
			'full_view' => true,
			'responses' => $responses,
		]);
	} else {
		// Show partial entity listing
		$content = elgg_view_entity($entity, [
			'full_view' => false,
			'class' => 'box',
		]);

		// Show comments
		$content .= elgg_view_comments($entity, true, [
			'entity' => $entity,
			'comment' => $comment,
			'active_tab' => 'comments',
			'show_add_form' => true,
			'expand_form' => true,
		]);
	}

	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'entity' => $entity,
		'filter' => false,
	));

	echo elgg_view_page($title, $layout, 'default', [
		'entity' => $entity,
	]);
}

