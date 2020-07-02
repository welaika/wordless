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

Wordless is tested with the enforced nodejs version and the shipped ``yarn.lock``
file. You're free to change version as you wish, but you'll
be on your own managing all the dependancies.

NVM
"""

In a Wordless theme you'll find an ``.nvmrc`` file; you can use
`NVM`_ node version manager to easily switch to the right node version.

Setup of NVM is not covered in this documentation.

Once set up, you can use

  .. code-block:: bash

      nvm use

.. _NVM: https://github.com/nvm-sh/nvm
