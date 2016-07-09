# No-Cache

#### A [Craft CMS](http://craftcms.com) Twig extension for disabling caching inside cache blocks

```twig
{% cache %}
	This will be cached
	{% nocache %}
		This won't be
	{% endnocache %}
{% endcache %}
```

It also works when disabling the cache from included files:

```twig
{% cache %}
	This will be cached
	{% include 'template' %}
{% endcache %}
```

_template.twig:_
```twig
{% nocache %}
	This won't be
{% endnocache %}
```

## Example: User information

Here's a common situation. You have a header to your website, and in it you want to a menu of page links, as well as the current logged in user. Since your menu is being generated through database queries, you want to cache your entire header. But you need your user information section to not be cached.

You have two options. One is to segment your cache blocks up, like so:

```twig
{% cache %}
<header>
	<nav>
		<ul>
			{% nav link in craft.entries.section('menu') %}
				<li><a href="{{ link.url }}">{{ link.title }}</a></li>
			{% endnav %}
		</ul>
	</nav>
{% endcache %}
	{% if currentUser %}
		<div>Welcome, {{ currentUser.name }}</div>
	{% endif %}
</header>
```

The problem with this approach is, A) you can't do this if you're including templates with data you don't want to cache, and B) it looks god awful.

Using `{% nocache %}`, we can clean this up quite nicely:

```twig
{% cache %}
<header>
	<nav>
		<ul>
			{% nav link in craft.entries.section('menu') %}
				<li><a href="{{ link.url }}">{{ link.title }}</a></li>
			{% endnav %}
		</ul>
	</nav>
	{% nocache %}
	{% if currentUser %}
		<div>Welcome, {{ currentUser.name }}</div>
	{% endif %}
	{% endnocache %}
</header>
{% endcache %}
```

Ah, that's much better.

## Example: CSRF tokens

A fantastic security feature, but one that basically renders caching impossible to use. Often you might find yourself outputting a form to the frontend, but it's nested deep within a chain of template includes and macros. At the top of this chain you've conveniently wrapped a cache tag around it.

Well, now your CSRF tokens are going to be cached and there's basically nothing you can do about it. Using `{% nocache %}` tags, this is no longer a problem:

```twig
<form>
	{% nocache %}{{ getCsrfInput() }}{% endnocache %}
	...
</form>
```

Now you can include this form anywhere in your templates and not have to worry about your CSRF tokens being cached. As a side note, yes `{% nocache %}` tags _will_ work even when they are not inside a cache block (though try and avoid doing this as `{% nocache %}` tags _do_ add some overhead).

## Caveat

Content inside `{% nocache %}` blocks will render slightly different than normal. Variables declared outside of the `{% nocache %}` block will actually have their values cached for the duration of the cache block.

This causes an issue in situations like the following:

```twig
{% set article = craft.entries.section('news').first %}
{% cache %}
	...
	{% nocache %}
		{{ article.title }}
	{% endnocache %}
{% endcache %}
```

You would expect that if you were to change the title of the article, it will update inside the `{% nocache %}` block. This is not the case, as the article itself would be cached due to the cache block.

There's a few ways around this. You could move the `{% set articles %}` statement _within_ the cache block, so updating the article would cause the cache to bust. In situations where you are using the article inside the cache (but outside the `nocache` block) this is the preferred method, as you won't spend any database calls grabbing the article inside the `nocache` block.

```twig
{% cache %}
	{% set article = craft.entries.section('news').first %}
	...
	{% nocache %}
		{{ article.title }}
	{% endnocache %}
{% endcache %}
```

The other option is to query for the article inside the `nocache` block. This can be better than the above solution as updating the article title will not bust the cache. The difference in this situation is now the contents of the `nocache` block will cause a database call.

```twig
{% cache %}
	...
	{% nocache %}
		{% set article = craft.entries.section('news').first %}
		{{ article.title }}
	{% endnocache %}
{% endcache %}
```

Use your best judgement.
