# WMC SwiftMailer Twig bridge

This bundle provides an easy way to create email templates using Twig for the
SwiftMailer library.

This helper is inspired from
[FOSUB TwigSwiftMailer](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Mailer/TwigSwiftMailer.php).

If you're using FOS User Bundle, we also provide a mailer service drop-in
replacement to support our additional features.

## Installation

### With Symfony
The best way to install this extension is through composer:

First, require the bundle:

```sh
composer require wemakecustom/swiftmailer-twig-bundle "^1.0"
```

Second, enable it:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new WMC\SwiftmailerTwigBundle\WMCSwiftmailerTwigBundle(),
        // ...
    );
}
```

Third and finally, configure the web_directory parameter to use it:

```yaml
# config.yml

parameters:
    web_directory: %kernel.root_dir%/../web
```

and you're done.

#### FOS User Bundle integration


```yaml
# config.yml

fos_user:
    service:
        mailer: wmc.swiftmailer_twig.fosub
```


### With a pure Swiftmailer/Twig

The best way to install this extension is through composer:

First, require the bundle:

```sh
composer require wemakecustom/swiftmailer-twig-bundle "^1.0"
```

Then give the naming strategy to doctrine's configuration:

```php
<?php

$swiftMailerTemplateHelper = new \WMC\SwiftmailerTwigBundle\TwigSwiftHelper($twig, $web_directory);
```

## Usage

It allows a Swift_Message to be populated with a Twig template. The template
expects three blocks (`subject`, `body_html`, `body_text`). If any local image
(i.e. whose `src` attribute starting with a single forward-slash `/`) with the
class `inline-image` is found in the `body_html` block, it will be inlined in
the email, allowing some eMail clients to render the image more easily.

This helper is available as the service `wmc.swiftmailer_twig`. To use it,
invoke the method `populateMessage` with these three parameters:

  1. the `\Swift_Message`
  2. template name
  3. data array for the template

The helper depends on Symfony's component DomCrawler, Twig and SwiftMailer.


Example:

```php

$data = [];

// ...

$data['recipient'] = ['name' => 'Jonh Smith', 'email' => 'recipient@example.com'];

$message = $mailer->createMessage()->setTo(['recipient@example.com' => 'John Smith']);
$swiftMailerTemplateHelper->populateMessage($message, 'AppBundle:Mail:my_email.mail.twig', $data);
$mailer->send($message);
```

```twig
{# my_email.mail.twig #}

{% block subject -%}
   My email Subject
{%- endblock %}

{% block body_text %}
    Hello {{ recipient.name }},

    {# ... Awesome plain text email ... #}

    Best Regards,
    Keep being awesome!
{% endblock %}

{% block body_html %}
    <h3>Hello <strong>{{ recipient.name }}</strong>,</h3>

    <p>
         {# ... Awesome HTML email ... #}
    </p>

    <p>
        Best Regards,<br />
        <em>Keep being awesome!</em>
    </p>
{% endblock %}
```
