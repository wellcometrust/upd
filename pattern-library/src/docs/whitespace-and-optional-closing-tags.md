Many grid/layout components in this pattern library include an inline-block fallback for browsers that don't support flexbox. As such it is necessary to avoid whitespace between elements. There are three recommended ways of doing this:

1. When using elements with an optional closing tag (eg `li`), simply omit the closing tag.
2. Ensure there is no whitespace between the rendered elements. Consider using markup in the server-side code to indicate the whitespace must be avoided - for example: `... dapibus ut.</div><?php // Avoid whitespace between elements ?><div>Suspendisse maximus ...`
3. Use an HTML comment between elements - for example: `... dapibus ut.</div><!-- Avoid whitespace between elements --><div>Suspendisse maximus ...`. Note that this technique may not work correctly with some HTML minifiers (which may replace comments with whitespace).

Note that CSS-based methods of removing this whitespace have deliberately been avoided due to the limitations/requirements they place on typography.
 