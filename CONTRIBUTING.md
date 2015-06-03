# Contributing to the TGM Plugin Activation website

Please take a moment to review this document in order to make the contribution process easy and
effective for everyone involved.

Following these guidelines helps to communicate that you respect the time of the developers managing
and developing this open source project. In return, they should reciprocate that respect in addressing
your issue or assessing patches and features.


## Using the issue tracker

The [issue tracker](https://github.com/TGMPA/TGM-Plugin-Activation/issues) is the preferred channel
for changes: spelling mistakes, wording changes, new content and generally [submitting pull requests](#pull-requests),
but please respect the following restrictions:


<a name="pull-requests"></a>
## Pull Requests

Pull requests are a great way to add new content to the TGM Plugin Activation website, as well as updating
any browser issues or other style changes. Pretty much any sort of change is accepted if seen as constructive.

Adhering to the following this process is the best way to get your work included in the project:

1. [Fork](http://help.github.com/fork-a-repo/) the project, clone your fork, and configure the remotes:

   ```bash
   # Clone your fork of the repo into the current directory
   git clone https://github.com/<your-username>/TGM-Plugin-Activation.git
   # Navigate to the newly cloned directory
   cd TGM-Plugin-Activation
   # Assign the original repo to a remote called "upstream"
   git remote add upstream https://github.com/TGMPA/TGM-Plugin-Activation.git
   ```

2. If you cloned a while ago, get the latest changes from upstream:

   ```bash
   git checkout gh-pages
   git pull upstream gh-pages
   ```

3. Create a new topic branch (off the main project development branch) to contain your change or fix:

   ```bash
   git checkout -b <topic-branch-name>
   ```

4. Install the [Jekyll](https://github.com/jekyll/jekyll/) and [GitHub Pages](https://github.com/github/pages-gem)
   gems to preview locally.
   See [Using Jekyll with Pages](https://help.github.com/articles/using-jekyll-with-pages) for more information.

5. Commit your changes in logical chunks. Please adhere to these [git commit
   message guidelines](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html)
   or your content is unlikely be merged into the main project. Use Git's
   [interactive rebase](https://help.github.com/articles/interactive-rebase)
   feature to tidy up your commits before making them public.

6. Locally merge (or rebase) the upstream development branch into your topic branch:

   ```bash
   git pull [--rebase] upstream gh-pages
   ```

7. Push your topic branch up to your fork:

   ```bash
   git push origin <topic-branch-name>
   ```

8. [Open a Pull Request](https://help.github.com/articles/using-pull-requests/)
    with a clear title and description.


## Creating new posts, pages, faq questions

There is an example file for each document type available in the `_drafts` folder. These example
files contain detailed information on how to create new files, what metadata is needed and what
variables are available.


## Contributor Style Guide

1. Use American English spelling (*primary English repo only*).
2. Code samples should adhere to the Code samples should adhere to the
   [WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/).
3. You can mix [GitHub Flavored Markdown](http://github.github.com/github-flavored-markdown/) and html,
   but markdown is preferred. Only use html if the desired effect cannot be achieved using markdown.
4. Use language agnostic urls when refering to external websites such as the
   [php.net](http://php.net/urlhowto.php) manual.
5. All files should be encoded as UTF-8 without BOM.


## Troubleshooting local test environment

* For local testing, you may need to adjust the following values in the `_config.yml` file to get it working:
   - Set `site.baseurl` to '' (empty string).
   - Set `site.tgmpa.url` to `http://localhost:4000` or for testing on your own fork of the repo
     to `http://username.github.io/TGM-Plugin-Activation`

* Help on [GitHub build errors](https://help.github.com/articles/troubleshooting-github-pages-build-failures)


## Useful reference material

If you've not contributed to a Jekyll-based gh-pages website before, you may need to read up on the techniques used.

Here is a list of relevant documentation:

* [Jekyll Documentation](http://jekyllrb.com/docs/)
* [GitHub Flavoured MarkDown](https://guides.github.com/features/mastering-markdown/)
* [Kramdown Markdown Parser Quick Reference](http://kramdown.gettalong.org/quickref.html)
* [Kramdown Markdown Parser Syntax Documentation](http://kramdown.gettalong.org/syntax.html)
* [Liquid Templating Introduction](https://github.com/Shopify/liquid/wiki/Liquid-for-Designers)
* [Liquid Templating Documentation](https://docs.shopify.com/themes/liquid-documentation/)


This site uses GitHub enriched Jekyll metadata, see the below for more information:

* [Repository metadata on GitHub Pages](https://help.github.com/articles/repository-metadata-on-github-pages/)
* [Additional metadata](https://github.com/blog/1833-github-pages-3)
* [Releases metadata for GitHub Pages](https://github.com/blog/1996-releases-metadata-for-github-pages)
* [Repository API Documentation](https://developer.github.com/v3/repos/)
* [Releases API Documentation](https://developer.github.com/v3/repos/releases/)
* [Contributors API Documentation](https://developer.github.com/v3/repos/#list-contributors)