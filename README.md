# PHP CSS Autoprefixer
A simple CSS autoprefixer written in PHP to prefix according your desires minified CSS code on the server side.

## Example

You can easily turn this code : 

```css
.myclass {
  color: red;
  clip-path: var(--primary);
  transition-duration: 3s;
}
@keyframes nameAnim {
  0%,
  100% {
    column-gap: 5px;
  }
}

```

Into this one :

```css
.myclass {
  color: red;
  clip-path: var(--primary);
  -webkit-clip-path: var(--primary);
  transition-duration: 3s;
  -webkit-transition-duration: 3s;
  -o-transition-duration: 3s;
}
@-webkit-keyframes nameAnim {
  0%,
  100% {
    column-gap: 5px;
    -webkit-column-gap: 5px;
    -moz-column-gap: 5px;
  }
}
@keyframes nameAnim {
  0%,
  100% {
    column-gap: 5px;
    -webkit-column-gap: 5px;
    -moz-column-gap: 5px;
  }
}
```

**Warning**:
- This plugin class doesn't support the @media and all the other CSS Rules (except @keyframes)
- You have to minify your CSS before the compilation

You can submit an pull request request if you are interested in any of these features.

## Usage
```php
$css = '.myclass{color:red;clip-path:var(--primary);transition-duration:3s;}@keyframes nameAnim{0%,100%{column-gap:5px;}}';

$prefixed_css = CSS_Autoprefixer::compile($css);
```

## License

[GPL](https://www.gnu.org/licenses/gpl-3.0.html) licensed.