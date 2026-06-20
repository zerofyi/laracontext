# LaraContext 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zerofyi/laracontext.svg?style=flat-square)](https://packagist.org/packages/zerofyi/laracontext)
[![Total Downloads](https://img.shields.io/packagist/dt/zerofyi/laracontext.svg?style=flat-square)](https://packagist.org/packages/zerofyi/laracontext)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version Support](https://img.shields.io/badge/php-%5E8.2-blue.svg?style=flat-square)](https://php.net)

`LaraContext` is a high-density architectural blueprint compiler engineered specifically for Laravel applications.

Instead of dumping raw repositories into LLM windows and blowing past token limits, LaraContext parses live framework structures in memory via Reflection engines to provide AI assistants with maximum architectural insight for minimum token consumption.

---

## 🔥 Key Features

- **Dynamic Schema Inspection:** Sniffs database drivers natively to list tables, field sets, and casting data types instantly without crashing on unmigrated structures.
- **Deep Reflection Extraction:** Traverses Eloquent models dynamically to map public relationship graphs (`hasMany`, `belongsTo`, etc.).
- **Automated Validation Injections:** Maps controller action definitions to resolve injected `FormRequest` validation rules, creating field-level request documentation for the AI.
- **Fail-Safe Containment:** Gracefully falls back past unresolvable bindings or custom parameters, continuing the blueprint without killing the run.

---

## 💾 Installation

```bash
composer require zerofyi/laracontext
```

The package relies on Laravel's standard auto-discovery protocols out of the box.

---

## 🚀 Usage

Compile the application's context blueprint:

```bash
php artisan context:generate
```

This generates a high-density, structured Markdown profile named `ai-context.md` right in the root working directory of your host application.

| Flag | Description |
|---|---|
| `--output=custom.md` | Overrides the default file name and target destination path. |
| `--no-routes` | Bypasses endpoint mapping loops to ensure maximum token space containment. |

---

## 📄 License

The MIT License (MIT). Please see the [License File](LICENSE) for more details.