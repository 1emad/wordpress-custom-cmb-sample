# Custom CMB (Custom Meta Boxes) in a WordPress Theme


## Context and problem

This code is extracted from a custom WordPress school theme. The site has multiple
page layouts and repeatable editorial sections, such as admissions steps and FAQs.
Editors needed to update the content from WordPress admin without changing PHP
markup or requiring a separate custom-fields plugin for each template.

## My approach

I used a small **theme-owned, schema-driven CMB (Custom Meta Boxes) system**:

1. **Field schemas** — Each section has a PHP array that declares the meta box,
   target post type, applicable template, and fields. The admissions page defines
   two `group` fields: steps (`number`, `title`, `text`) and FAQs (`question`, `answer`).
2. **Registration** — The CMB component loads schema files using a controlled
   directory scan and registers WordPress meta boxes with `add_meta_box()`.
3. **Reusable admin UI** — A field-type renderer builds repeatable group controls
   from the schema. Admin JavaScript handles adding, removing, and reordering
   rows; the PHP code serializes the field's data into a hidden textarea.
4. **Persistence** — The save handler checks WordPress's post nonce, rejects
   autosaves, verifies `edit_post` permissions, and delegates field-specific
   sanitization to shared helpers. The full theme includes strict value
   sanitization, JSON validation, and special handling to avoid accidentally
   losing repeater data after a malformed submission.
5. **Frontend integration** — Templates load grouped values from post metadata
   (via a shared JSON-decoding helper), loop through the data, and escape
   text/HTML using `esc_html()`, `esc_attr()`, and `wp_kses_post()`.

## Why I designed it this way

- **Separation of concerns:** editor field definitions, framework logic, and
  HTML templates live in separate components.
- **Reuse:** new page sections can be defined via field schemas rather than
  copying a complete `add_meta_box()` / save implementation.
- **WordPress-native:** built around WordPress hooks, permissions, and post meta.
- **Maintainable:** a predictable folder structure makes it easier to trace a
  field from admin registration to frontend output.
- **Flexible data modeling:** JSON-backed group fields make ordered repeated
  content straightforward to edit and render.

## Trade-offs and what I would improve today

A theme-owned meta-box system keeps the presentation and the content schema close,
but couples editorial data to a specific theme. For content that must outlive the
theme, I would move the reusable CMB engine into a small custom plugin. For highly
queryable repeaters, I would also reconsider JSON storage and use a structure
that supports native metadata queries or dedicated entities. I would add focused
tests around nonce/capability enforcement, field sanitization, and malformed
repeater submissions.

## File guide

| File | Original source | Purpose |
| --- | --- | --- |
| `01-admissions-field-schema.php` | `Components/CMB/posts/admissions.php` | The editable fields are declared in an array. |
| `02-schema-loader-and-meta-box-registration.php` | `Components/CMB/bootstrap.php`, methods `extract_files()` and `custom_meta_box()` | Discovers schemas and registers native WordPress meta boxes. |
| `03-secure-save-handler.php` | `Components/CMB/bootstrap.php`, method `save_custom_meta_box_data()` | Permission checks, nonce checks, and metadata persistence. |
| `04-repeatable-group-field-renderer.php` | `Components/Fields/bootstrap.php`, method `group()` | Renders the flexible list of editor-defined group items. |
| `05-front-end-output.php` | `Templates/page/admissions.php` | How group data is rendered and escaped. |

