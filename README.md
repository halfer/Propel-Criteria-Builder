Propel Criteria Builder
===

This is a small PHP project that I built in 2006 to help beginner Propel users convert
simple SQL statements into Criteria code, which is a query object in Propel.

A demo of the project runs here: http://propel.jondh.me.uk/.

Updated Dec 2014
---

At the time of its development I was rather pleased with the
parser module, but it's now pretty old, and doesn't have unit tests. It works on symfony 1.0.x,
which needs core tweaks to work on PHP 5.5, and Propel has gone through several substantial
revisions since 1.2, for which this was built.

In particular, Propel now uses a Query object as a better way to filter your `SELECT` query, though
Criteria classes do still exist. So, this project has been rather superceded!

Updated Sep 2018
---

The Propel project itself has mostly died now, so anyone starting a new project
with an ORM requirement should probably be looking at Doctrine. Still, the Propel team
do make commits very occasionally, so you never know!
