# KiiConf

KiiConf is composed by two main programs:

- the editor
- the configurator

The editor is meant for admins and it is used to generate the layout file for the configurator.

The configurator loads the layout and lets the user configure the keyboard layers.

**NOTE:** the applications are set up in debug mode by calling the ``APP`` constructor with the first parameter set to ``true``. In production it should be set to ``false`` or simply undefined.