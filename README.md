# No-Cache

#### A [Craft CMS](http://craftcms.com) Twig extension to escape caching inside cache blocks

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

## Caveat

Content inside `{% nocache %}` blocks will lose access to the current context. Variables, macros, and anything else declared outside the block will be invisible inside the block – except for anything in the global context.

```twig
{% set variable = 5 %}
{% cache %}
	...
	{% nocache %}
		{{ variable }} {# Will throw an error #}
		{{ now|datetime }} {# Will output fine as `now` is a global variable #}
	{% endnocache %}
{% endcache %}
```

Fear not, for you see, context _can_ be passed explicitly:

```twig
{% set variable = 5 %}
{% cache %}
	...
	{% nocache {variable: variable} %}
		{{ variable }} {# Will output 5 #}
	{% endnocache %}
{% endcache %}
```

There is another caveat here though, and that is the passed context itself will be cached. So in the above example, if `variable` changes value, but the cache block hasn't been cleared, then the `nocache` block will still render with `variable` being `5`.

Due to this, you should only use variables declared inside the cache block so it's clear that it's infact being cached.

```twig
{% cache %}
	{% set variable = 5 %}
	...
	{% nocache {variable: variable} %}
		{{ variable }} {# Will output 5 #}
	{% endnocache %}
{% endcache %}
```
