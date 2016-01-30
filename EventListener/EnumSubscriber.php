<?php

namespace Preemiere\CoreBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\Config\ResourceCheckerConfigCacheFactory;

/**
 * @author Konstantin Grachev <ko@grachev.io>
 */
class EnumSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\Config\ResourceCheckerConfigCacheFactory
     */
    private $cacheFactory;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param \Symfony\Component\Config\ResourceCheckerConfigCacheFactory $cacheFactory
     * @param string $cacheDir
     */
    public function __construct(ResourceCheckerConfigCacheFactory $cacheFactory, string $cacheDir)
    {
        $this->cacheFactory = $cacheFactory;
        $this->cacheDir = $cacheDir;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        foreach ($metadata->fieldMappings as $column => $meta) {
            if (in_array('enum', $meta, true)) {
                $class = $this->getEnumClass($metadata->name, $meta);
                $type = str_replace('\\', '', $class);

                $cache = $this->cacheFactory->cache($this->cacheDir . '/enum/' . sha1($class) . '.php',
                    function (ResourceCheckerConfigCache $cache) use ($class, $type) {
                        $cache->write($this->getTemplate([$type, $class]));
                    }
                );

                Type::addType($type, 'Preemiere\CoreBundle\Doctrine\\' . $type);
                $metadata->fieldMappings[$column]['type'] = $type;

                require_once $cache->getPath();
            }
        }
    }

    /**
     * @param $name
     * @param array $meta
     *
     * @return string
     */
    public function getEnumClass($name, array $meta)
    {
        $peaces = explode('\\', $name);
        $bundle = $peaces[0];
        $entity = end($peaces);

        if (array_key_exists('options', $meta)) {
            $options = $meta['options'];

            if (1 === count($options) && class_exists($class = current($options))) {
                dump('Options 0');
                return $class;
            } elseif (array_key_exists('enum', $options) && class_exists($class = $options['enum'])) {
                dump('Optons enum');
                return $class;
            }
        }

        if (class_exists($class = sprintf('%s\\Enum\\%s%sEnum', $bundle, $entity, ucfirst($meta['fieldName'])))) {
            dump('Bundle Enum Entity FieldName Enum');
            return $class;
        } elseif (class_exists($class = sprintf('%s\\Enum\\%sEnum', $bundle, $entity))) {
            dump('Bundle Enum Entity Enum');
            return $class;
        }

        throw new ORMInvalidArgumentException(sprintf('Class for enum field "%s" of "%s" not found', $meta['fieldName'], $name));
    }

    /**
     * @param array $replace
     *
     * @return string
     */
    public function getTemplate(array $replace)
    {
        $search = ['__TYPE__', '__CLASS__'];

        return str_replace($search, $replace, <<<EOF
<?php
namespace Preemiere\CoreBundle\Doctrine;
class __TYPE__ extends EnumType
{
    const ENUM = '__TYPE__';
    protected \$class = '__CLASS__';
}
EOF
        );
    }
}
