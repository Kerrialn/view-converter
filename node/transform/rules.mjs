/**
 * Ordered rule registry — first matching rule wins per node.
 * Add new rules here to extend the transformer.
 */
import iife          from './rules/iife.mjs';
import varToLet      from './rules/var-to-let.mjs';
import ready         from './rules/ready.mjs';
import staticJquery  from './rules/static.mjs';
import chainFn       from './rules/chain-fn.mjs';
import chainTwoLevel from './rules/chain-two-level.mjs';
import chain         from './rules/chain.mjs';

export default [
    iife,
    varToLet,
    ready,
    staticJquery,
    chainFn,
    chainTwoLevel,
    chain,
];
