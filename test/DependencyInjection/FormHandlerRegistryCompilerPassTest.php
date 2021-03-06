<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\FormHandlerBundle\DependencyInjection\Compiler;

use Hostnet\Bundle\FormHandlerBundle\ParamConverter\FormParamConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Hostnet\Bundle\FormHandlerBundle\DependencyInjection\Compiler\FormHandlerRegistryCompilerPass
 */
class FormHandlerRegistryCompilerPassTest extends TestCase
{
    protected function setUp()
    {
        if (!interface_exists('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface')) {
            $this->markTestSkipped(
                'Sensio Extra bundle is not installed.'
            );
        }
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($service_name, $tagged_services)
    {
        $container = new ContainerBuilder();
        $container->setDefinition($service_name, new Definition());
        $container->setDefinition('hostnet.form_handler.registry', new Definition(null, [null, null]));

        foreach ($tagged_services as $id => $tag) {
            $container->register($id)->addTag($tag, ['tests']);
        }

        $pass = new FormHandlerRegistryCompilerPass();
        $pass->process($container);

        self::assertCount(
            \count($tagged_services),
            $container->getDefinition('hostnet.form_handler.registry')->getArgument(1)
        );
    }

    public function processDataProvider()
    {
        return [
            ['form_handler.param_converter', []],
            [FormParamConverter::class, []],
            ['form_handler.param_converter', ['test.service' => 'form.handler', 'test.phpunit' => 'form.handler']],
            [FormParamConverter::class, ['test.service' => 'form.handler', 'test.phpunit' => 'form.handler']],
        ];
    }
}
