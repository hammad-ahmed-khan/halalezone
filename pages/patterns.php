<?php
// Конфигурация маршрутов URL проекта.

$routes = array
(
	// Главная страница сайта (http://localhost/)
	array(

		// паттерн в формате Perl-совместимого реулярного выражения
		'pattern' => '~^/upload$~',
		// Имя класса обработчика
		'class' => 'Upload',
		// Имя метода класса обработчика
		'method' => 'upload'
	),

	// Главная страница сайта (http://localhost/)
	array(

		// паттерн в формате Perl-совместимого реулярного выражения
		'pattern' => '~^/login$~',
		// Имя класса обработчика
		'class' => 'Login',
		// Имя метода класса обработчика
		'method' => 'login'
	),

	array(

		// паттерн в формате Perl-совместимого реулярного выражения
		'pattern' => '~^/register$~',
		// Имя класса обработчика
		'class' => 'Register',
		// Имя метода класса обработчика
		'method' => 'register'
	),

	array(
	// паттерн в формате Perl-совместимого реулярного выражения
	'pattern' => '~^/$~',
	// Имя класса обработчика 
	'class' => 'Index',
	// Имя метода класса обработчика
	'method' => ''
	),

	// Страница (http://localhost/application)
	array(
		'pattern' => '~^/application1$~',
		'class' => 'Index',
		'method' => 'application1',
		),

	// Страница (http://localhost/application)
	array(
		'pattern' => '~^/application$~',
		'class' => 'Index',
		'method' => 'application',
		),
		
	// Страница (http://localhost/application)
	array(
	'pattern' => '~^/calendar$~',
	'class' => 'Index',
	'method' => 'calendar',
	),

	// Страница (http://localhost/products)
	array(
	'pattern' => '~^/products$~',
	'class' => 'Index',
	'method' => 'products',
	),
	
	// Страница (http://localhost/administration)
	array(
		'pattern' => '~^/groups$~',
		'class' => 'Index',
		'method' => 'groups',
	),

	// Страница (http://localhost/ingredients)
	array(
	'pattern' => '~^/ingredients$~',
	'class' => 'Index',
	'method' => 'ingredients',
	),

	// Страница (http://localhost/qm)
	array(
		'pattern' => '~^/qm$~',
		'class' => 'Index',
		'method' => 'qm',
	),

	// Страница (http://localhost/audit)
	array(
		'pattern' => '~^/audit$~',
		'class' => 'Index',
		'method' => 'audit',
	),

	// Страница (http://localhost/administration)
	array(
		'pattern' => '~^/administration$~',
		'class' => 'Index',
		'method' => 'administration',
	),

	array(
		'pattern' => '~^/companies$~',
		'class' => 'Index',
		'method' => 'companies',
		),
	
	array(
		'pattern' => '~^/paingreds$~',
		'class' => 'Index',
		'method' => 'paIngreds',
		),
	
	// Страница (http://localhost/administration)
	array(
		'pattern' => '~^/settings$~',
		'class' => 'Index',
		'method' => 'settings',
	),
	
	array(
		'pattern' => '~^/process_status$~',
		'class' => 'Index',
		'method' => 'processStatus',
	),

	array(
		'pattern' => '~^/branches$~',
		'class' => 'Index',
		'method' => 'branches',
	),

	array(
		'pattern' => '~^/tickets$~',
		'class' => 'Index',
		'method' => 'tickets',
	),

	array(
		'pattern' => '~^/customer_service$~',
		'class' => 'Index',
		'method' => 'customerService',
	),

	array(
		'pattern' => '~^/tasks$~',
		'class' => 'Index',
		'method' => 'tasks',
	),

	array(
		'pattern' => '~^/facilities$~',
		'class' => 'Index',
		'method' => 'facilities',
	),

	array(
		'pattern' => '~^/preferences$~',
		'class' => 'Index',
		'method' => 'preferences',
	),

	array(
		'pattern' => '~^/training$~',
		'class' => 'Index',
		'method' => 'training',
	),

	array(
		'pattern' => '~^/faq_manager$~',
		'class' => 'Index',
		'method' => 'faqManager',
	),

	array(
    'pattern' => '~^/support$~',
    'class' => 'Index',
    'method' => 'faq',
),

);
?>