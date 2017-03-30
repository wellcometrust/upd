This site uses uses several SVG icons. In order for the icons to be linked correctly, the icon file (`images/icons.svg`) must be included (server-side) immediately after the opening body tag. The  should be wrapped in a `u-visually-hidden` element so that the included element does not affect the page layout. For example:

```
...
<body>

    <div class="u-visually-hidden">

        <svg xmlns="http://www.w3.org/2000/svg">

            ...

        </svg>

    </div>
...

```