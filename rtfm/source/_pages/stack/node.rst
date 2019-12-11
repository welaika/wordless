.. _Node:

Nodejs
======

`Nodejs`_ is used for all the front-end build chain.

You need to have Node installed on your machine. The setup is not
covered by this documentation.

.. _Nodejs: https://nodejs.org/en/

Version
"""""""

Each release of Wordless is bound to a node version. It is declared
inside ``package.json``.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :language: json
    :caption: pakage.json
    :emphasize-lines: 2
    :lineno-start: 10
    :lines: 10-12

Wordless is tested with the enforced nodejs version and the shipped ``yarn.lock``
file. You're free to change version as you wish, but you'll
be on your own managing all the dependancies.

NVM
"""

Using NVM is **strongly recommended**.

In a Wordless theme you'll find an ``.nvmrc`` file; you can use
`NVM`_ node version manager to easily switch to the right node version.

Installing  NVM is as simple as

  .. code-block:: bash

      curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.36.0/install.sh | bash

but you can read more at https://github.com/nvm-sh/nvm#install--update-script.

.. note::
    v0.36.0 is the most recent version at time of writing

Once set up, you can install the required node version

  .. code-block:: bash

      nvm install x.x.x

where ``x.x.x`` is the version reported in previous paragraph. Then, once you'll
be ready to work, use it with within your theme

  .. code-block:: bash

      nvm use

.. _NVM: https://github.com/nvm-sh/nvm
