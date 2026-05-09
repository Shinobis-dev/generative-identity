# Generative Identity

> Deterministic, hash-based visual identities for headless projects, AI agents, and technical blogs.

Most blogs and projects use stock photos or AI-generated images that are interchangeable. A generative system creates images that are unique to your content. No stock photos. No DALL-E. No Midjourney. Just pure algorithms turning your text into unique, verifiable SVGs and PNGs.

Every image is **deterministic** — the same seed content always produces the same image.

## 🚀 Two Ways to Use It

This repository houses both a zero-friction web tool and the core backend engine.

### 1. The Web Generator (Vanilla JS)
Type any text and watch the pattern generate in real-time. Perfect for generating quick avatars for AI nodes, scripts, or social accounts.
👉 **[Try it live in your browser →](https://shinobis-dev.github.io/generative-identity/)**
*No backend, no dependencies, no `npm install`. 100% client-side.*

### 2. The Core Engine (PHP)
The automated system used on [shinobis.com](https://shinobis.com) to generate Open Graph images dynamically based on article content.

---

## 🧠 Philosophy: Why build this?

> Your visual identity shouldn't depend on a stock library or a diffusion model. It should be born from your content. Because if your content is unique, your visual identity should be too.

In a world where AI models evaluate content originality, having images that are a direct, computationally provable function of your text is an authenticity signal that generic images cannot replicate. 

- **Unique:** No other post produces the same pattern.
- **Lightweight:** 2-8KB for SVG, instant generation for PNG.
- **Brandable:** Consistent aesthetic rules across all generations.
- **Verifiable:** Computationally provable that the image belongs to specific content.

📖 **Read the full manifesto:** [Generative Identity: why my blog's images are born from content](https://shinobis.com/en/generative-identity-blog-images-born-from-content-no-stock-photos)

---

## ⚙️ How The Algorithm Works

The generator takes text inputs (like a title and content body) and translates them into a unique visual pattern.

1. **Geometry from Hashes:** The system creates an MD5 hash of the title and SHA1 of the content. It uses each hex character to determine whether a cell in a 32×32 grid gets filled, and with what style (dark, accent, or light). 
2. **Color from Length:** Shorter strings get warm gold tones. Longer content shifts through blue, purple, terracotta, and mint. The palette stays within a cohesive family.
3. **The Center Signature:** A kanji character (忍, "shinobi") sits in a cleared center zone. This can be replaced with any character or logo.
4. **Accent Dots:** 12 small circles are placed pseudo-randomly using a seed derived from the title hash, ensuring exact placement across regenerations.

---

## 💻 Server-Side Integration (PHP)

If you want to use the core engine to automate image generation on your server.

### Output Formats
- **PNG** (1200×1200) — For social media, Open Graph, Twitter Cards.
- **SVG** (animated) — For inline display on the blog, with entrance animations and pulsing cells.

### Requirements
- PHP 7.4+ with GD extension.
- For CJK center character: a Noto Sans CJK font (optional, falls back to a diamond shape).
- *Zero external dependencies. No Composer. No npm. Just PHP.*

### Quick Start
```bash
git clone [https://github.com/Shinobis-dev/generative-identity.git](https://github.com/Shinobis-dev/generative-identity.git)
cd generative-identity
php generate.php --title="My First Post" --content="Your article content here"

### As a standalone script

```php
<?php
require_once 'generative-identity.php';

$path = generateImage(
    id: 1,
    title: 'My Blog Post Title',
    content: 'The full text of your blog post...',
    lang: 'en',
    outputDir: __DIR__ . '/output/'
);
echo "Image saved to: $path";
```

### Auto-generation in a Blog Template

```php
// In your post template, auto-generate on first view:
$imgPath = '/uploads/social/post-' . $post['id'] . '-' . $lang . '.png';

if (!file_exists(__DIR__ . $imgPath)) {
    require_once 'generative-identity.php';
    generateImage($post['id'], $post['title'], $post['content'], $lang);
}
echo '<img src="' . $imgPath . '" alt="' . $post['title'] . '">';
```

## Configuration & Color Palette

Edit the constants at the top of `generative-identity.php`:

```php
define('IMG_SIZE', 1200);       // Output size in pixels (square)
define('GRID_COLS', 32);        // Grid density (more = finer pattern)
define('CENTER_CHAR', '忍');    // Character in the center (or empty for none)
```

Colors shift based on content length (character count):

| Content Length | Color | RGB |
|---|---|---|
| < 2,000 chars | Gold | 200, 170, 100 |
| 2,000 - 4,000 | Cool Blue | 100, 170, 200 |
| 4,000 - 6,000 | Purple | 170, 100, 200 |
| 6,000 - 8,000 | Terracotta | 200, 120, 100 |
| 8,000+ | Mint | 100, 200, 150 |

You can customize these in the `$colors` array.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

**Shinobis** — UX/UI Designer with 10+ years in fintech.

- Blog: [shinobis.com](https://shinobis.com)
- Twitter: [@shinobis_com](https://x.com/shinobis_com)
- LinkedIn: [shinobis-ai](https://www.linkedin.com/company/shinobis-ai)
