<?php

declare(strict_types=1);

namespace Andi\GraphQL\Spiral\Common;

use GraphQL\Type\Definition as Webonyx;
use GraphQL\Type\Schema;

final class SchemaWarmupper
{
    public static function warmup(Schema $schema): void
    {
        $allTypes = [];

        $types = $schema->getConfig()->getTypes();
        if (is_callable($types)) {
            $types = $types();
        }

        foreach ($types as $type) {
            self::warmupType(Schema::resolveType($type), $allTypes);
        }

        foreach ($schema->getDirectives() as $directive) {
            foreach ($directive->args as $arg) {
                self::warmupType($arg->getType(), $allTypes);
            }
        }

        if ($type = $schema->getQueryType()) {
            self::warmupType($type, $allTypes);
        }

        if ($type = $schema->getMutationType()) {
            self::warmupType($type, $allTypes);
        }

        if ($type = $schema->getSubscriptionType()) {
            self::warmupType($type, $allTypes);
        }

        $schema->getTypeMap();
    }

    private static function warmupType(Webonyx\Type $type, array &$allTypes): void
    {
        if ($type instanceof Webonyx\WrappingType) {
            self::warmupType($type->getInnermostType(), $allTypes);

            return;
        }
        assert($type instanceof Webonyx\NamedType);
        /**
         * @psalm-suppress NoInterfaceProperties
         * @psalm-suppress UndefinedPropertyFetch
         */
        $name = $type->name;
        if (isset($allTypes[$name])) {
            return;
        }

        $allTypes[$name] = true;

        if ($type instanceof Webonyx\EnumType) {
            $enumValues = $type->getValues();
            if (is_callable($type->config['values'])) {
                $values = [];
                foreach ($enumValues as $value) {
                    $values[$value->name] = [
                        'value' => $value->value,
                        'description' => $value->description,
                        'deprecationReason' => $value->deprecationReason,
                    ];
                }
                $type->config['values'] = $values;
            }
            return;
        }

        if ($type instanceof Webonyx\UnionType) {
            $type->config['types'] = $type->getTypes();
            foreach ($type->config['types'] as $member) {
                self::warmupType($member, $allTypes);
            }
            return;
        }

        if ($type instanceof Webonyx\InputObjectType) {
            $type->config['fields'] = $type->getFields();
            foreach ($type->config['fields'] as $field) {
                self::warmupType($field->getType(), $allTypes);
            }
            return;
        }

        if ($type instanceof Webonyx\ImplementingType) {
            $interfaces = $type->getInterfaces();
            foreach ($interfaces as $interface) {
                self::warmupType($interface, $allTypes);
            }
            assert($type instanceof Webonyx\ObjectType || $type instanceof Webonyx\InterfaceType);
            $type->config['interfaces'] = $interfaces;
        }

        if ($type instanceof Webonyx\HasFieldsType) {
            $fields = $type->getFields();
            foreach ($fields as $field) {
                foreach ($field->args as $arg) {
                    self::warmupType($arg->config['type'] = $arg->getType(), $allTypes);
                }
                self::warmupType($field->getType(), $allTypes);
            }

            assert($type instanceof Webonyx\ObjectType || $type instanceof Webonyx\InterfaceType);
            $type->config['fields'] = $fields;
        }
    }
}
