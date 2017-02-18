<?php

namespace Grachevko\EnumBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMInvalidArgumentException;
use Grachevko\Enum\Enum;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\Config\ResourceCheckerConfigCacheFactory;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
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
    public function __construct(ResourceCheckerConfigCacheFactory $cacheFactory, $cacheDir)
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

                Type::addType($type, 'Grachevko\EnumBundle\Doctrine\\' . $type);
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

            if (1 === count($options) && $class = $this->checkClass(current($options))) {
                return $class;
            } elseif (array_key_exists('enum', $options) && $class = $this->checkClass($options['enum'])) {
                return $class;
            }
        }

        if ($class = $this->checkClass(sprintf('%s\\Enum\\%s%sEnum', $bundle, $entity, ucfirst($meta['fieldName'])))) {
            return $class;
        } elseif ($class = $this->checkClass(sprintf('%s\\Enum\\%sEnum', $bundle, $entity))) {
            return $class;
        }

        throw new ORMInvalidArgumentException(sprintf('Class for enum field "%s" of "%s" not found', $meta['fieldName'], $name));
    }

    /**
     * @param $namespace
     *
     * @return bool
     */
    private function checkClass($namespace)
    {
        if (class_exists($namespace) && in_array(Enum::class, class_parents($namespace, true), true)) {
            return $namespace;
        }

        return false;
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
namespace Grachevko\EnumBundle\Doctrine;
class __TYPE__ extends EnumType
{
    const ENUM = '__TYPE__';
    protected \$class = '__CLASS__';
}
EOF
        );
    }
}
