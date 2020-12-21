# No-Cache

#### A [Craft CMS](http://craftcms.com) Twig extension that escapes caching inside cache blocks

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

If you need to reference variables outside of the `nocache` tag, you will need to pass a context – much like how the `include` tag works when passing variables. Note that you do not have to pass global variables, such as `craft` or `currentUser`, but you will have to import your macros again.

```twig
{% set variable = 5 %}
{% nocache with {x: variable} %}
    The following value should be 5: {{ x }}
{% endnocache %}
```

## Requirements

No-Cache 2.0.6 requires Craft CMS 3.1.24 or later.

## Installation

No-Cache can be installed from the [Craft Plugin Store](https://plugins.craftcms.com/) or with [Composer](https://packagist.org/).

### Craft Plugin Store
Open your project's control panel, navigate to the Plugin Store, search for No-Cache and click Install.

### Composer
Open your terminal, navigate to your project's root directory and run the following command:
```
composer require ttempleton/craft-nocache
```
Then open your project's control panel, navigate to Settings &rarr; Plugins, find No-Cache and click Install.

## Example: User information

Say you have a list of products you want to show on your page. Under each product, you want an "add to cart" button. However, you only want to show this button _if_ a user is logged in. Not only that, but you also want to disable the button if the user already has it in their cart. Unfortunately you're outputting 20 products a page with images, so caching the list seems like the responsible thing to do.

```twig
{% cache %}
{% for product in craft.entries().section('products').limit(20).all() %}
    <article>
        <figure>{{ product.image.one().img }}</figure>
        <h1>{{ product.title }}</h1>
        {% if currentUser %}
            <button{{ currentUser.cart.id(product.id).count() > 0 ? ' disabled' }}>Add to cart</button>
        {% endif %}
    </article>
{% endfor %}
{% endcache %}
```

Now we have a problem. The cache around the list of products will cause the `currentUser` logic to essentially not work, since they'll be cached along with the products. You can't isolate the user logic by separating things into multiple cache blocks, since you're in a loop, and the whole point was to cache the database call that grabs the product entries. So you either have to apply your user checking in Javascript (far from ideal), or disregard caching altogether.

With `nocache` tags you can fix this very easily:

```twig
{% cache %}
{% for product in craft.entries().section('products').limit(20).all() %}
    <article>
        <figure>{{ product.image.one().img }}</figure>
        <h1>{{ product.title }}</h1>
        {% nocache with {productId: product.id} %}
        {% if currentUser %}
            <button{{ currentUser.cart.id(productId).count() > 0 ? ' disabled' }}>Add to cart</button>
        {% endif %}
        {% endnocache %}
    </article>
{% endfor %}
{% endcache %}
```

The `nocache` block will allow you to cache the entire product list, but still perform your user logic outside of the cache. It also allows passing of context, so you can still refer to products and entries inside the `nocache` block and access their properties, _without_ any additional database calls.

## Example: CSRF tokens

A fantastic security feature, but one that basically renders caching impossible to use. Often you might find yourself outputting a form to the frontend, but it's nested deep within a stack of template includes and macros. At the top of this stack you've conveniently wrapped a cache tag around it.

Well, now your CSRF tokens are going to be cached and there's basically nothing you can do about it. Using `nocache` tags, this is no longer a problem:

```twig
<form>
    {% nocache %}{{ csrfInput() }}{% endnocache %}
    ...
</form>
```

Now you can include this form anywhere in your templates and not have to worry about your CSRF tokens being cached. As a side note, yes `nocache` tags _will_ work even when they are not inside a cache block (though try and avoid doing this as `nocache` tags _do_ add some overhead).

## Caveat

Content inside `nocache` blocks will render slightly different than normal. Variables declared outside of the `nocache` block will actually have their values cached for the duration of the cache block.

This causes an issue in situations like the following:

```twig
{% set article = craft.entries().section('news').one() %}
{% cache %}
    ...
    {% nocache with {article: article} %}
        {{ article.title }}
    {% endnocache %}
{% endcache %}
```

You would expect that if you were to change the title of the article, it will update inside the `nocache` block. This is not the case, as the article itself would be cached due to the cache block.

There's a few ways around this. You could move the `{% set articles %}` statement _within_ the cache block, so updating the article would cause the cache to bust. In situations where you are using the article inside the cache (but outside the `nocache` block) this is the preferred method, as you won't spend any database calls grabbing the article inside the `nocache` block.

```twig
{% cache %}
    {% set article = craft.entries().section('news').one() %}
    ...
    {% nocache with {article: article} %}
        {{ article.title }}
    {% endnocache %}
{% endcache %}
```

The other option is to query for the article inside the `nocache` block. This can be better than the above solution as updating the article title will not bust the cache. The difference in this situation is now the contents of the `nocache` block will cause a database call.

```twig
{% cache %}
    ...
    {% nocache %}
        {% set article = craft.entries().section('news').one() %}
        {{ article.title }}
    {% endnocache %}
{% endcache %}
```

Every situation will be different, so use your best judgement.

---

*Big thanks to [Ben Fleming](https://github.com/benjamminf) for creating No-Cache and for letting me take it over.*
