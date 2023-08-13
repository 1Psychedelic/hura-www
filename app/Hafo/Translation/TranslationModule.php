<?php

namespace Hafo\Translation;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\Translation\Translator\DefaultTranslator;
use Nette\Localization\ITranslator;

class TranslationModule implements Module {

    private $langResolver;

    private $langs = [];

    function __construct($langs = ['cs', 'en'], callable $langResolver = NULL) {
        $this->langResolver = $langResolver;
        $this->langs = $langs;
    }

    function install(ContainerBuilder $builder) {
        $builder->addFactories([
            DefaultTranslator::class => function(Container $c) {
                return new DefaultTranslator($this->langs);
            },
            Translator::class => function(Container $c) {
                return $c->get(DefaultTranslator::class);
            },
            ITranslator::class => function (Container $c) {
                return $c->get(Translator::class);
            }
        ]);

        $builder->addDecorators([
            DefaultTranslator::class => function(DefaultTranslator $translator, Container $c) {
                if($this->langResolver !== NULL) {
                    $translator->setLanguage(call_user_func_array($this->langResolver, [$c]));
                }
                $translator->addVocabulary('common.', __DIR__ . '/lang_en.php', 'en');
                $translator->addVocabulary('common.', __DIR__ . '/lang_cs.php', 'cs');
            }
        ]);
    }

}
