<?php

namespace hypeJunction\Interactions;

$entity = elgg_extract('entity', $vars);

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:max_comment_depth') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('interactions:settings:max_comment_depth:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[max_comment_depth]',
	'value' => $entity->max_comment_depth,
	'options' => array(5, 4, 3, 2, 1),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:comment_form_position') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('interactions:settings:comment_form_position:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[comment_form_position]',
	'value' => $entity->comment_form_position,
	'options_values' => array(
		'before' => elgg_echo('interactions:settings:comment_form_position:before'),
		'after' => elgg_echo('interactions:settings:comment_form_position:after'),
	),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:comments_order') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('interactions:settings:comments_order:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[comments_order]',
	'value' => $entity->comments_order,
	'options_values' => array(
		'asc' => elgg_echo('interactions:settings:comments_order:chronological'),
		'desc' => elgg_echo('interactions:settings:comments_order:reverse_chronological'),
	),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:comments_load_style') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[comments_load_style]',
	'value' => $entity->comments_load_style,
	'options_values' => array(
		'load_newer' => elgg_echo('interactions:settings:comments_load_style:load_newer'),
		'load_older' => elgg_echo('interactions:settings:comments_load_style:load_older'),
	),
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:comments_limit') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[comments_limit]',
	'value' => $entity->comments_limit,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('interactions:settings:comments_load_limit') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'params[comments_load_limit]',
	'value' => $entity->comments_load_limit,
));
echo '</div>';