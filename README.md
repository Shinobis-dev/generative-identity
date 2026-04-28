# Generative Identity

A PHP system that generates unique visual identities for blog posts based on their content. Every image is deterministic — the same content always produces the same image. No stock photos. No DALL-E. No Midjourney. Just algorithms and your words.

**[See it in action →](https://shinobis.com)**

![Example of generative images](https://shinobis.com/uploads/social/post-71-es.png)

## How It Works

The generator takes two inputs: a title and content. From those, it produces a unique visual pattern.

**Color is derived from content length.** Shorter posts get warm gold tones. Longer posts shift through blue, purple, terracotta, and mint. The palette stays within a cohesive family but varies per post.

**Geometry is derived from title + content hashes.** The system creates an MD5 hash of the title and SHA1 of the content, then uses each hex character to determine whether a cell in a 32×32 grid gets filled, and with what style (dark, accent, or light). The result is a QR-like pattern that's unique to each piece of content.

**The center features a signature element.** A kanji character (忍, "shinobi") sits in a cleared center zone. You can replace this with any character, logo, or shape.

**Accent dots add texture.** 12 small circles are placed pseudo-randomly using a seed derived from the title hash, ensuring consistent placement across regenerations.

## Output Formats

- **PNG** (1200×1200) — For social media, Open Graph, Twitter Cards
- **SVG** (animated) — For inline display on the blog, with entrance animations and pulsing cells

## Quick Start

```bash
git clone https://github.com/Shinobis-dev/generative-identity.git
cd generative-identity
php generate.php --title="My First Post" --content="Your article content here"
```

This generates `output.png` in the current directory.

## Usage

### As a standalone script

```php
<?php
require_once 'generative-identity.php';

// Generate PNG
$path = generateImage(
    id: 1,
    title: 'My Blog Post Title',
    content: 'The full text of your blog post...',
    lang: 'en',
    outputDir: __DIR__ . '/output/'
);

echo "Image saved to: $path";
```

### As part of a blog

```php
// In your post template, auto-generate on first view:
$imgPath = '/uploads/social/post-' . $post['id'] . '-' . $lang . '.png';
if (!file_exists(__DIR__ . $imgPath)) {
    require_once 'generative-identity.php';
    generateImage($post['id'], $post['title'], $post['content'], $lang);
}
echo '<img src="' . $imgPath . '" alt="' . $post['title'] . '">';
```

## Configuration

Edit the constants at the top of `generative-identity.php`:

```php
define('IMG_SIZE', 1200);       // Output size in pixels (square)
define('GRID_COLS', 32);        // Grid density (more = finer pattern)
define('CENTER_CHAR', '忍');    // Character in the center (or empty for none)
```

### Color Palette

Colors shift based on content length (character count):

| Content Length | Color | RGB |
|---|---|---|
| < 2,000 chars | Gold | 200, 170, 100 |
| 2,000 - 4,000 | Cool Blue | 100, 170, 200 |
| 4,000 - 6,000 | Purple | 170, 100, 200 |
| 6,000 - 8,000 | Terracotta | 200, 120, 100 |
| 8,000+ | Mint | 100, 200, 150 |

You can customize these in the `$colors` array.

## Requirements

- PHP 7.4+ with GD extension (included in standard PHP)
- For CJK center character: a Noto Sans CJK font (optional, falls back to a diamond shape)

No external dependencies. No Composer. No npm. Just PHP.

## Why Generative Identity?

Most blogs use stock photos or AI-generated images that are interchangeable between any site. A generative system creates images that are:

- **Unique to your content** — No other post produces the same pattern
- **Deterministic** — Same content = same image, every time
- **Lightweight** — 2-8KB for SVG, instant generation for PNG
- **Brandable** — Consistent color family across all posts
- **Verifiable** — Computationally provable that the image belongs to specific content

In a world where AI models evaluate content originality, having images that are a direct function of your text is an authenticity signal that stock photos cannot replicate.

## Philosophy

> Your visual identity shouldn't depend on a stock library or a diffusion model. It should be born from your content. Because if your content is unique, your visual identity should be too.

Read more: [Generative Identity: why my blog's images are born from content](https://shinobis.com/en/generative-identity-blog-images-born-from-content-no-stock-photos)

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

**Shinobis** — UX/UI Designer with 10+ years in fintech.

- Blog: [shinobis.com](https://shinobis.com)
- Twitter: [@shinobis_com](https://x.com/shinobis_com)
- LinkedIn: [shinobis-ai](https://www.linkedin.com/company/shinobis-ai)
