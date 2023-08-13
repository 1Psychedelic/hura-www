<?php

namespace Hafo\NetteHelpers\ContainerModule;

use Hafo\DI\Container;
use Hafo\DI\ContainerBuilder;
use Hafo\DI\Module;
use Hafo\NetteBridge\Forms\FormFactory;
use Hafo\NetteBridge\Forms\Validators\CzechPersonalIdValidator;
use Nette\Forms\Controls\CsrfProtection;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\Form;
use Nette\Forms\Validator;
use Hafo\Utils\Arrays;
use Nette\Localization\ITranslator;
use Hafo\Translation\Translator\DefaultTranslator;

class FormsModule implements Module {

    private $config = [
        'translate' => TRUE,
        'protectCSRF' => TRUE,
        'renderer' => NULL
    ];

    function __construct(array $config = []) {
        foreach($config as $key => $val) {
            $this->config[$key] = $val;
        }
    }

    function install(ContainerBuilder $builder) {

        if($this->config['translate']) {
            Validator::$messages = [
                Form::EQUAL => 'form.message.equal',
                Form::NOT_EQUAL => 'form.message.notEqual',
                Form::FILLED => 'form.message.required',
                Form::BLANK => 'form.message.blank',
                Form::MIN_LENGTH => 'form.message.minLength',
                Form::MAX_LENGTH => 'form.message.maxLength',
                Form::LENGTH => 'form.message.length',
                Form::EMAIL => 'form.message.email',
                Form::URL => 'form.message.url',
                Form::INTEGER => 'form.message.integer',
                Form::FLOAT => 'form.message.float',
                Form::MIN => 'form.message.min',
                Form::MAX => 'form.message.max',
                Form::RANGE => 'form.message.range',
                Form::MAX_FILE_SIZE => 'form.message.maxFileSize',
                Form::MAX_POST_SIZE => 'form.message.maxPostSize',
                Form::MIME_TYPE => 'form.message.mimeType',
                Form::IMAGE => 'form.message.image',
                SelectBox::VALID => 'form.message.choice',
                UploadControl::VALID => 'form.message.upload',
                CsrfProtection::PROTECTION => 'form.message.csrf',
                CzechPersonalIdValidator::getRule() => 'form.message.czechPersonalId'
            ];
        }

        $builder->addFactories([
            FormFactory::class => function (Container $c) {
                $factory = new FormFactory\SimpleFormFactory;
                return $factory;
            },
            \Nette\Application\UI\Form::class => function(Container $c) {
                return $c->get(FormFactory::class)->create();
            }
        ]);

        $builder->addDecorators([
            FormFactory::class => function(FormFactory $factory, Container $c) {
                if($this->config['protectCSRF']) {
                    $factory = new FormFactory\ProtectedFormFactory($factory);
                }
                if($this->config['renderer']) {
                    $factory = new FormFactory\StyledFormFactory($factory, function() {
                        if(is_string($this->config['renderer'])) {
                            return new $this->config['renderer'];
                        } else {
                            return $this->config['renderer'];
                        }
                    });
                }
                if($this->config['translate']) {
                    $factory = new FormFactory\TranslatedFormFactory($factory, $c->get(ITranslator::class));
                }
                return $factory;
            },
            DefaultTranslator::class => function(DefaultTranslator $translator, Container $c) {
                $translator->addVocabulary('form.', __DIR__ . '/../Forms/lang_en.php', 'en');
                $translator->addVocabulary('form.', __DIR__ . '/../Forms/lang_cs.php', 'cs');
            },
        ]);
    }

}
