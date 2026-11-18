<?php
/**
 * Admissions hub page fields.
 */
$arr = [
	'id'        => 'hero_for_admissions',
	'title'     => 'Admissions Header',
	'callback'  => '',
	'ptype'     => ['page'],
	'context'   => 'normal',
	'repeatable'=> false,
	'priority'  => 'high',
	'templates' => ['admissions'],
	'options'   => [
		[ 'type' => 'text', 'id' => 'page_subtitle', 'title' => 'Subtitle' ],
		[
			'type'  => 'group',
			'id'    => 'admissions_steps',
			'title' => 'Admissions Process Steps',
			'options' => [
				[ 'type' => 'text', 'id' => 'number', 'title' => 'Step Number (e.g. 01)' ],
				[ 'type' => 'text', 'id' => 'title', 'title' => 'Step Title' ],
				[ 'type' => 'textarea', 'id' => 'text', 'title' => 'Step Description' ],
			],
		],
		[
			'type'  => 'group',
			'id'    => 'admissions_faqs',
			'title' => 'Admissions FAQs',
			'options' => [
				[ 'type' => 'text', 'id' => 'question', 'title' => 'Question' ],
				[ 'type' => 'editor', 'id' => 'answer', 'title' => 'Answer' ],
			],
		],
	],
];
return $arr;
