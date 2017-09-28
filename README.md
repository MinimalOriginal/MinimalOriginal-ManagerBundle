MinimalOriginal ManagerBundle
========

The manager bundle for Minimal

Register bundle
========
$bundles = [
    ...
    new MinimalOriginal\ManagerBundle\MinimalManagerBundle(),
];

Register routes
========
minimal_manager:
    resource: "@MinimalManagerBundle/Resources/config/routing.yml"
    prefix:   /
