<meta name="theme-color" content="#FFFFFF" />
<link rel="icon" type="image/svg+xml" href="/uploads/brewcafe-icon-v3.svg">
<title><?php echo htmlspecialchars((string)($pageTitle ?? 'BrewCafe'), ENT_QUOTES, 'UTF-8'); ?></title>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<style type="text/tailwindcss">
  @theme {
  --font-display: "DM Sans", ui-sans-serif, system-ui, sans-serif;
  --font-sans: "DM Sans", ui-sans-serif, system-ui, sans-serif;
  --color-cream: #FFFFFF;
  --color-latte: #F7F7F7;
  --color-coffee: #111111;
  --color-coffee-dark: #000000;
  --color-ink: #111111;
  --color-muted: #666666;
  --color-line: #E5E5E5;
  --color-primary: var(--color-ink);
  --color-surface: #F7F7F7;
}
</style>
<style>
  :root {
    --color-cream: #FFFFFF;
    --color-latte: #F7F7F7;
    --color-coffee: #111111;
    --color-coffee-dark: #000000;
    --color-ink: #111111;
    --color-muted: #666666;
    --color-line: #E5E5E5;
    --color-primary: var(--color-ink);
    --color-surface: #F7F7F7;
    --color-primary-hover: #000000;
    --shadow-card: none;
    --btn-radius: 6px;
    --card-radius: 8px;
    --input-radius: 6px
  }

  .bg-cream {
    background-color: #FFFFFF
  }

  .bg-latte {
    background-color: #F7F7F7
  }

  html {
    scroll-behavior: smooth
  }

  html,
  body {
    background: #fff
  }

  body {
    color: var(--color-ink);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    display: flex;
    flex-direction: column
  }

  main {
    flex: 1
  }

  .font-display {
    font-family: "PT-Serif", ui-serif, Georgia, Cambria, "Times New Roman", Times, serif
  }

  ::selection {
    background: var(--color-ink);
    color: #fff
  }

  :focus-visible {
    outline: 2px solid var(--color-ink);
    outline-offset: 2px;
    border-radius: 6px
  }

  .card {
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: var(--card-radius);
    box-shadow: none
  }

  .card-lift {
    box-shadow: none
  }

  .btn,
  .btn-primary,
  .btn-dark,
  .btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    font-weight: 500;
    min-height: 44px;
    padding: .65rem 1.15rem;
    border-radius: var(--btn-radius);
    transition: background .15s, color .15s, border-color .15s;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    font-size: 15px
  }

  .btn-primary {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary)
  }

  .btn-primary:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover)
  }

  .btn-dark {
    background: var(--color-ink);
    color: #fff;
    border-color: var(--color-ink)
  }

  .btn-dark:hover {
    background: #000;
    border-color: #000
  }

  .btn-ghost {
    background: #fff;
    border-color: var(--color-line);
    color: var(--color-ink)
  }

  .btn-ghost:hover {
    background: var(--color-surface);
    border-color: var(--color-ink)
  }

  .input {
    width: 100%;
    padding: .65rem .9rem;
    min-height: 44px;
    border-radius: var(--input-radius);
    border: 1px solid var(--color-line);
    background: #fff;
    box-sizing: border-box;
    font-size: 15px;
    color: var(--color-ink)
  }

  .input::placeholder {
    color: var(--color-muted)
  }

  .input:focus {
    outline: 2px solid var(--color-ink);
    outline-offset: 1px;
    border-color: var(--color-ink);
    box-shadow: none
  }

  .label {
    display: block;
    font-size: .875rem;
    font-weight: 500;
    color: var(--color-muted)
  }

  .eyebrow {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .16em;
    color: var(--color-muted)
  }

  .photo {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    background: #F7F7F7;
    border-radius: 6px
  }

  .photo-block {
    width: 100%;
    aspect-ratio: 4/3;
    border-radius: 6px;
    background: #F7F7F7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #111;
    font-weight: 700;
    font-size: 1.5rem;
    letter-spacing: .02em
  }

  .chip {
    display: inline-block;
    background: #F7F7F7;
    border: 1px solid var(--color-line);
    border-radius: 6px;
    padding: .25rem .6rem;
    font-size: .8rem;
    color: var(--color-ink)
  }

  .navlink {
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    padding: .5rem .85rem;
    min-height: 42px;
    border-radius: 6px;
    font-weight: 500;
    color: var(--color-ink);
    text-decoration: none;
    font-size: 15px
  }

  .navlink:hover {
    background: var(--color-surface)
  }

  .navlink-active {
    background: var(--color-ink) !important;
    color: #fff !important
  }

  .status {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: .25rem .6rem;
    border: 1px solid;
    border-radius: 6px;
    font-size: .75rem;
    font-weight: 700;
    line-height: 1;
    text-transform: capitalize
  }

  .alert {
    border: 1px solid;
    padding: .75rem 1rem;
    border-radius: 8px;
    font-size: .875rem
  }

  .table-wrap {
    overflow-x: auto;
    border: 1px solid var(--color-line);
    border-radius: 8px;
    background: #fff;
    box-shadow: none
  }

  .bg-stone-900 {
    background-color: #111111 !important
  }

  .text-stone-900 {
    color: #111111 !important
  }

  .bg-stone-100 {
    background-color: #F7F7F7 !important
  }

  .text-stone-600,
  .text-stone-500 {
    color: #666666 !important
  }

  .text-stone-400,
  .text-stone-300 {
    color: #666666 !important
  }

  .text-stone-700 {
    color: #111111 !important
  }

  .border-stone-900 {
    border-color: #111111 !important
  }

  .bg-coffee {
    background-color: #111111 !important
  }

  .border-coffee {
    border-color: #111111 !important
  }

  .text-coffee {
    color: #111111 !important
  }

  .rounded-full {
    border-radius: 6px !important
  }

  .rounded-xl,
  .rounded-lg {
    border-radius: 8px !important
  }

  .rounded-md {
    border-radius: 6px !important
  }

  .border-stone-900\/10 {
    border-color: #E5E5E5 !important
  }

  [class*="divide-stone-900"]>* {
    border-color: #E5E5E5 !important
  }

  .border-stone-900\/15 {
    border-color: #E5E5E5 !important
  }

  .border-stone-900\/20 {
    border-color: #E5E5E5 !important
  }

  .border-stone-200 {
    border-color: #E5E5E5 !important
  }

  .hover\:bg-stone-900\/5:hover {
    background-color: #F7F7F7 !important
  }

  .file\:bg-stone-900 {
    --tw-file-bg: #111111
  }

  @media (prefers-reduced-motion:reduce) {

    *,
    *::before,
    *::after {
      animation-duration: .01ms !important;
      transition-duration: .01ms !important;
      scroll-behavior: auto !important
    }
  }
</style>