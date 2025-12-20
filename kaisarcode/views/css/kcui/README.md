# KCUI

This is an aesthetically vintage, but nonetheless technically modern CSS UI library, oriented to system interfaces and semantic HTML, decoupling the styles from the markup obsessively.

## Features

- **Semantic-first styling** that treats `header`, `main`, `section`, `article`, and friends as first-class citizens. No div soup required.
- **Vintage system aesthetic** powered by `vars.css`, giving you quick control over palettes, radii, and spacing without touching component CSS.
- **Comprehensive base reset** to normalize typographic rhythm, inputs, dialogs, and tables for consistent rendering across browsers.
- **Utility accents** (`.prm`, `.inf`, `.scc`, `.wrn`, `.dng`, etc.) to colorize any semantic block without breaking layout.
- **Native component polish** including dialogs with animated open states, validated inputs, progress bars, and responsive header/footer grids.

## Requirements

- Any modern browser with CSS Custom Properties (variables) support.
- Optional: a static server or build pipeline if you plan to bundle `kcui.css` with other assets.

## File Structure

| File | Description |
| ---- | ----------- |
| `vars.css` | Design tokens (font size, paddings, radii, palette, panel colors). Override here to re-skin KCUI. |
| `reset.css` | Opinionated reset aligned with KCUI’s semantic philosophy; imported by default. |
| `kcui.css` | Core library with panels, typography, layout helpers, utilities, and motion rules. |
| `demo.html` | Minimal semantic showcase demonstrating articles, sections, forms, and dialogs using KCUI. |

## Getting Started

- Copy the `css/kcui/` directory into your project.
- Include `kcui.css` (it auto-imports `vars.css` and `reset.css`):

```html
<link rel="stylesheet" href="css/kcui/kcui.css">
```

- Write semantic HTML. KCUI styles native blocks automatically, so a simple layout like the one below already looks composed:

```html
<header>
  <h1><a href="/">KCUI</a></h1>
  <nav><menu><li><a href="#docs">Docs</a></li></menu></nav>
</header>
<main>
  <article>
    <header><h2>Article Title</h2></header>
    <section>
      <fieldset>
        <legend>Controls</legend>
        <input type="text" placeholder="Semantic first">
        <button class="btn">Run</button>
      </fieldset>
    </section>
  </article>
</main>
```

## Customization

- Override any CSS variable from `vars.css` in a parent scope (e.g., `:root` or `.theme-alt`) to recolor or resize components.
- Apply color utility classes (`.prm`, `.sec`, `.inf`, `.scc`, `.wrn`, `.dng`, `.sys`, etc.) directly on semantic elements to shift emphasis without extra wrappers.
- Combine `section:has(> article)` layouts for auto-fit grids. KCUI already defines responsive gap handling.

## Demo

Open `css/kcui/demo.html` in your browser to explore:

- Header + nav built with `menu`.
- Main article with forms, datalist, and semantic footer notes.
- Related content section showcasing responsive articles.
- Native `<dialog>` styled via KCUI with open-state animation.

## Usage Tips

- Keep markup semantic: KCUI intentionally avoids relying on `.container` divs.
- Lean on `fieldset` + `legend` for grouped controls; KCUI enhances them automatically.
- Use native dialogs (`<dialog>`) for modals-the library handles positioning, backdrop, and transitions.
- Respect prefers-reduced-motion: KCUI only animates when the user allows it.

## License

Released under the GNU General Public License v3.0. See [`LICENSE`](LICENSE) for the full text.

## Author

- **Name:** KaisarCode
- **Website:** <https://kaisarcode.com>
- **Email:** <kaisar@kaisarcode.com>
- **GitHub:** <https://github.com/kaisarcode>
