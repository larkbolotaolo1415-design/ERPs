<?php

/**
 * Safely evaluate a mathematical formula string using provided variables.
 *
 * @param string $formula   The formula (e.g. "gross_pay * (0.05 / 2) + bonus - tax")
 * @param array  $variables Associative array of variable names and their values
 * @return float|int        The evaluated result
 * @throws Exception        If formula is invalid or evaluation fails
 */
function evaluateFormula(string $formula, array $variables)
{
    // Replace variable names with their numeric values
    $expression = preg_replace_callback(
        '/\b[a-zA-Z_][a-zA-Z0-9_]*\b/',
        function ($matches) use ($variables) {
            $varName = $matches[0];
            return isset($variables[$varName]) ? $variables[$varName] : '0';
        },
        $formula
    );

    // Basic safety check – only allow numeric, math, and parentheses symbols
    if (preg_match('/[^0-9+\-.*\/()% ]/', $expression)) {
        throw new Exception("Invalid characters detected in formula.");
    }

    try {
        // Safely evaluate using eval within a local scope
        $result = eval("return ($expression);");
        if ($result === false) {
            throw new Exception("Error evaluating formula.");
        }
        return $result;
    } catch (Throwable $e) {
        throw new Exception("Formula evaluation failed: " . $e->getMessage());
    }
}
