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

## Caveat

Content inside `{% nocache %}` blocks will lose access to the current context. Variables, macros, and anything else declared outside the block will be invisible inside the block – except for anything in the global context.

```twig
{% set variable = 5 %}
{% cache %}
	...
	{% nocache %}
    	{{ variable }} {# <- Will throw an error #}
        {{ now|datetime }} {# <- Will output fine as `now` is a global variable #}
    {% endnocache %}
{% endcache %}
```
