<?php

namespace hypeJunction\Interactions;

use ElggEntity;
use ElggObject;
use ElggRiverItem;

/**
 * @access private
 */
class InteractionsService {

	/**
	 * Replace the default comments block with an interactions component
	 * @see elgg_view_comments()
	 *
	 * @param string $hook        "comments"
	 * @param string $entity_type "all"
	 * @param string $return      View
	 * @param type   $params      Additional parameters
	 * @return string
	 */
	public static function replaceCommentsBlock($hook, $entity_type, $return, $params) {
		return elgg_view('page/components/interactions', $params);
	}

	/**
	 * Creates a commentable object associated with river items whose object is not ElggObject
	 *
	 * @param string $event
	 * @param string $type
	 * @param ElggRiverItem $river
	 * @return true
	 */
	public static function createRiverObject($event, $type, $river) {
		create_actionable_river_object($river);
		return true;
	}

	/**
	 * Creates an object associated with a river item for commenting and other purposes
	 * This is a workaround for river items that do not have an object or have an object that is group or user
	 *
	 * @param ElggRiverItem $river River item
	 * @return RiverObject|false
	 */
	public static function createActionableRiverObject(ElggRiverItem $river) {

		if (!$river instanceof ElggRiverItem) {
			return false;
		}

		$object = $river->getObjectEntity();
		if (!$object instanceof ElggObject) {
			$ia = elgg_set_ignore_access(true);

			$object = new RiverObject();
			$object->owner_guid = $river->subject_guid;
			$object->container_guid = $river->subject_guid;
			$object->access_id = $river->access_id;
			$object->river_id = $river->id;
			$object->save();

			elgg_set_ignore_access($ia);
		}

		return $object;
	}

	/**
	 * Get an actionable object associated with the river item
	 * This could be a river object entity or a special entity that was created for this river item
	 *
	 * @param ElggRiverItem $river River item
	 * @return ElggObject|false
	 */
	public static function getRiverObject(ElggRiverItem $river) {

		if (!$river instanceof ElggRiverItem) {
			return false;
		}

		$object = $river->getObjectEntity();
		if ($object instanceof ElggObject) {
			return $object;
		}

		// wrapping this in ignore access so that we do not accidentally create duplicate
		// river objects
		$ia = elgg_set_ignore_access(true);
		$objects = elgg_get_entities_from_metadata(array(
			'types' => RiverObject::TYPE,
			'subtypes' => array(RiverObject::SUBTYPE, 'hjstream'),
			'metadata_name_value_pairs' => array(
				'name' => 'river_id',
				'value' => $river->id,
				'operand' => '='
			),
			'limit' => 1,
		));
		$guid = ($objects) ? $objects[0]->guid : false;
		elgg_set_ignore_access($ia);

		if (!$guid) {
			$object = create_actionable_river_object($river);
			$guid = $object->guid;
		}

		return get_entity($guid);
	}

	/**
	 * Get interaction statistics
	 *
	 * @param ElggEntity $entity Entity
	 * @return array
	 */
	public static function getStats($entity) {

		if (!$entity instanceof ElggEntity) {
			return array();
		}

		$stats = array(
			'comments' => array(
				'count' => $entity->countComments()
			),
			'likes' => array(
				'count' => $entity->countAnnotations('likes'),
				'state' => (elgg_annotation_exists($entity->guid, 'likes')) ? 'after' : 'before',
			)
		);

		return elgg_trigger_plugin_hook('get_stats', 'interactions', array('entity' => $entity), $stats);
	}

	/**
	 * Get entity URL wrapped in an <a></a> tag
	 * @return string
	 */
	public static function getLinkedEntityName($entity) {
		if (elgg_instanceof($entity)) {
			return elgg_view('output/url', array(
				'text' => $entity->getDisplayName(),
				'href' => $entity->getURL(),
				'is_trusted' => true,
			));
		}
		return '';
	}

	/**
	 * Get configured comments order
	 * @return string
	 */
	public static function getCommentsSort() {
		$user_setting = elgg_get_plugin_user_setting('comments_order', 0, 'hypeInteractions');
		$setting = $user_setting ? : elgg_get_plugin_setting('comments_order', 'hypeInteractions');
		if ($setting == 'asc') {
			$setting = 'time_created::asc';
		} else if ($setting == 'desc') {
			$setting = 'time_created::desc';
		}
		return $setting;
	}

	/**
	 * Get configured loading style
	 * @return string
	 */
	public static function getLoadStyle() {
		$user_setting = elgg_get_plugin_user_setting('comments_load_style', 0, 'hypeInteractions');
		return $user_setting ? : elgg_get_plugin_setting('comments_load_style', 'hypeInteractions');
	}

	/**
	 * Get comment form position
	 * @return string
	 */
	public static function getCommentsFormPosition() {
		$user_setting = elgg_get_plugin_user_setting('comment_form_position', 0, 'hypeInteractions');
		return $user_setting ? : elgg_get_plugin_setting('comment_form_position', 'hypeInteractions');
	}
	/**
	 * Get number of comments to show
	 *
	 * @param string $partial Partial or full view
	 * @return string
	 */
	public static function getLimit($partial = true) {
		if ($partial) {
			$limit = elgg_get_plugin_setting('comments_limit', 'hypeInteractions');
			return $limit ? : 3;
		} else {
			$limit = elgg_get_plugin_setting('comments_load_limit', 'hypeInteractions');
			return $limit && $limit < 20 ? $limit : 20;
		}
	}

	/**
	 * Calculate offset till the page that contains the comment
	 *
	 * @param int     $count   Number of comments in the list
	 * @param int     $limit   Number of comments to display
	 * @param Comment $comment Comment entity
	 * @return int
	 */
	public static function calculateOffset($count, $limit, $comment = null) {

		$order = self::getCommentsSort();
		$style = self::getLoadStyle();

		if ($comment instanceof Comment) {
			$thread = new Thread($comment);
			$offset = $thread->getOffset($limit, $order);
		} else if (($order == 'time_created::asc' && $style == 'load_older')
			|| ($order == 'time_created::desc' && $style == 'load_newer')) {
			// show last page
			$offset = $count - $limit;
			if ($offset < 0) {
				$offset = 0;
			}
		} else {
			// show first page
			$offset = 0;
		}

		return (int) $offset;
	}

}
