<?php

namespace NetteAddons;

/**
 * Hierarchie výjimek
 * -----------------------------------------------------------------------------
 *
 * - Exception
 *     - RuntimeException = důležitý je typ výjimky pro přesné zachytávání; nelze jí předejít
 *         - NetteAddons\InvalidStateException
 *         - NetteAddons\DuplicateEntryException
 *         - NetteAddons\IOException
 *             - NetteAddons\FileNotFoundException
 *             - NetteAddons\DirectoryNotFoundException
 *     - LogicException = chyba v kódu, který volá metodu, která tuto výjimku vyhodila; lze jí předejít
 *         - InvalidArgumentException
 *             - NetteAddons\InvalidArgumentException
 *                 - NetteAddons\ArgumentOutOfRangeException
 *         - NetteAddons\NotImplementedException
 *         - NetteAddons\NotSupportedException
 *             - NetteAddons\DeprecatedException
 *         - NetteAddons\StaticClassException
 *
 * @author Jan Tvrdík
 */


// === Runtime exceptions ======================================================

/**
 * Výjimka vyhazovaná v případě, že volání metody je vzhledem k stavu objektu
 * neplatné (např. některé parametry ještě nejsou nastaveny) nebo je proběhlo
 * ve špatný nebo nevhodný čas.
 */
class InvalidStateException extends \RuntimeException
{

}



/**
 * Výjimky vyhazovaná v případě, že se nepodaří zapsat záznam (obvykle) do DB
 * kvůli unikátnímu indexu.
 */
class DuplicateEntryException extends \RuntimeException
{

}



/**
 * Výjimky vyhazovaná v případě, že se dojdek IO chybě.
 */
class IOException extends \RuntimeException
{

}



/**
 * Výjimka vyhazovaná při neexistenci souboru.
 */
class FileNotFoundException extends IOException
{

}



/**
 * Výjimka vyhazovaná při neexistenci složky.
 */
class DirectoryNotFoundException extends IOException
{

}



// === Logic exceptions ========================================================

/**
 * Výjimka vyhazovaná v případě, že je metodě předán neplatný argument.
 * (Např. předání jiného než očekávaného typu.)
 */
class InvalidArgumentException extends \InvalidArgumentException
{

}



/**
 * Výjimka vyhazovaná v případě, že je metodě předán argument, který nespadá
 * do množiny povolených hodnot.
 */
class ArgumentOutOfRangeException extends InvalidArgumentException
{

}



/**
 * Výjimka vyhazovaná v případě, že volaná metoda nebo její část není ještě
 * implementovaná.
 */
class NotImplementedException extends \LogicException
{

}



/**
 * Výjimka vyhazovaná v případě, že požadovaná činnost není podporovaná.
 */
class NotSupportedException extends \LogicException
{

}



/**
 * Výjimka vyhazovaná v případě, že volaná metoda nebo způsob jejího volání
 * je zastaralý.
 */
class DeprecatedException extends NotSupportedException
{

}



/**
 * Výjimka vyhazovaná pří pokusu o vytvoření instance statické třídy.
 */
class StaticClassException extends \LogicException
{

}
