# Упрощенная версия RBAC, основанная на файлах (аналог файлового RBAC из Yii)

Мне не понравилась стандартная реализация RBAC в Yii2, слишком уж они все усложнили. В частности, для не очень больших проектов, где прав доступа не очень много и они в процессе жизни приложения практически не меняются, этот подход на мой взгляд слишком громоздок. Поэтому я решил запилить реализацию RBAC на основе файлов, похожую на ту, что была в первом Yii.

Этот код служит только для проверки прав доступа. Здесь нет возможности программно создавать иерархию прав и сохранять ее. Иерархия описывается вручную в файле настроек.

## Инсталляция
Устанавливается с посощью [composer](http://getcomposer.org/download/).

Для установки выполняем команду

```
php composer.phar require --prefer-dist m00nk/yii2-rbac  "*"
```

или добавляем

```json
"m00nk/yii2-rbac": "*"
```

в разделе `require` вашего composer.json файла.

## Настройка

```php
'components' => [
    ...
    'authManager' => [
		  'class' => 'm00nk\rbac\AuthManager',
		  'authFile' => '@app/config/auth.php' // путь к файлу с описанием иерархии прав доступа
		],
		...
],
```

## Пример файла прав доступа (@app/config/auth.php)

```php
return [

	// PERMISSIONS
    //-------------------------

	'permCreatePost' => [
		'type' => 0, // permission
		'description' => 'право на создание поста в блоге'
	],

	'permUpdatePost' => [
		'type' => 0,
		'description' => 'право на редактирование поста в блоге'
	],

	// TASKS
    //-------------------------

	'permUpdateOwnPost' => [
		'type' => 1, // task
		'description' => 'право на редактирование СЫВОЕГО поста в блоге'
		'rule' => 'return app\\models\\Post::mayEditPost($params["id"]);',
		'children' => [
			'permUpdatePost'
		]
	],

	// ROLES
    //-------------------------

	'author' => [
		'type' => 2, // role
		'description' => 'автор - может только создавать посты и редактировать только свои'
		'children' => [
			'permCreatePost',
			'permUpdateOwnPost'
		]
	],

	'moderator' => [
		'type' => 2, // role
		'description' => 'модератор - может только редактировать посты, зато чьи угодно'
		'children' => [
			'permUpdatePost'
		]
	],

];
```

## Проверка прав в коде
Проверка осуществлается стандартным способом:

```php
// проверяем, может ли юзер создавать посты
if(\Yii::$app->user->can('permCreatePost')) ...

// проверяем, может ли юзер редактировать текущий пост
// обратите внимание, что проверяется именно permUpdatePost, а не permUpdateOwnPost
if(\Yii::$app->user->can('permUpdatePost', ['id' => $post->id])) ...

```

Начиная с версии 2.1.0 можно в качестве правил использовать инлайн-функции:
```php
'rule' => function($params){ return app\models\Post::mayEditPost($params["id"]); },
```