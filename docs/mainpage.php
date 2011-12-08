<?php

  # Main Page documentation
/**
 * @mainpage Wordless, don't waste words on wordpress!
 *
 * @section intro_sec Introduction
 *
 * Welcome to the developer documentation for Wordless. Actually you are looking
 * at a complex WordPress plugin which make you create themes using the power of
 * HAML, SASS/SCSS, COMPASS, and CoffeScript, drastically speeding up your
 * development time!
 *
 * Automation is for robots, we are humans, after all! ;)
 *
 * Please refer to the <a href="https://github.com/welaika/wordless#readme">
 * online documentation</a> for more details and instructions.
 *
 * @section doxy_doc Doxygen documentation
 *
 * To know how to use Doxygen Documentation system please check the
 * <a href="http://www.stack.nl/~dimitri/doxygen/manual.html">Doxygen
 * Manual</a>.
 *
 * To search through all the commands available in Doxygen, please see the
 * <a href="http://www.stack.nl/~dimitri/doxygen/commands.html">Doxygen Command
 * List</a>.
 * 
 * @section groups Define groups used in Wordless documentation
 * 
 * Some groups are defined to collect informations about specific use classes
 * or functions:
 * - @ref helpers "Helpers Group": the group of all existent Wordless helpers.
 * - @ref helperclass "Helpers Classes"
 * - @ref helperfunc "Helpers Functions"
 *
 * @section self_cmds Defined command for Wordless documentation
 *
 * You can use some Doxygen commands create only for wordless:
 * - @b \@doubt: This section, which appears also in "Related Pages" is used to
 *   express doubt about code implementation.
 *
 * @see https://github.com/welaika/wordless
 */

  # Now will define some project-wide groups!
/**
 * @defgroup helpers Helpers 
 *   All the defined helpers ( classes, functions, ...) you can find in 
 *   Wordless.
 * 
 * @defgroup helperclass Helpers Classes
 *   All the defined helpers classes in Wordless.
 *   @ingroup helpers
 * 
 * @defgroup helperfunc Helpers Functions
 *   All the helpers functions in Wordless.
 *   @ingroup helpers
 * 
 */
