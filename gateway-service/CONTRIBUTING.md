# Contributing to Gateway Service

Thank you for your interest in contributing to the Gateway Service! This document provides guidelines for contributing to this project.

## 🚀 Getting Started

### Prerequisites
- Node.js 18 or higher
- npm 8 or higher
- Git

### Setup Development Environment

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/yourusername/microservice-gateway.git
   cd microservice-gateway
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Copy environment variables**
   ```bash
   cp .env.example .env
   ```

4. **Start development server**
   ```bash
   npm run dev
   ```

## 📝 Development Guidelines

### Code Style

We use ESLint and Prettier for code formatting. Please ensure your code passes all linting checks:

```bash
# Check linting
npm run lint

# Auto-fix linting issues
npm run lint:fix

# Check formatting
npm run format:check

# Auto-format code
npm run format
```

### Commit Messages

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

- `feat:` New features
- `fix:` Bug fixes
- `docs:` Documentation changes
- `style:` Code style changes (formatting, etc.)
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

Examples:
```
feat: add rate limiting configuration
fix: resolve JWT verification issue
docs: update API documentation
```

### Testing

All code should be tested. We use Node.js built-in test runner:

```bash
# Run tests
npm test

# Run tests in watch mode
npm run test:watch
```

### Adding New Routes

When adding new proxy routes:

1. **Update configuration** in `src/config.js`
2. **Create middleware** if custom authentication is needed
3. **Register proxy** in `src/plugins/proxy.js`
4. **Add tests** for the new functionality
5. **Update documentation**

Example:
```javascript
// 1. Add to config
services: {
  newService: process.env.NEW_SERVICE_URL || 'http://localhost:9003',
}

// 2. Create guard (if needed)
// src/middlewares/serviceGuards/newServiceGuard.js

// 3. Register proxy
// src/plugins/proxy.js
app.register(async (instance) => {
  instance.addHook('preHandler', authGuard);
  await instance.register(httpProxy, {
    upstream: config.services.newService,
    prefix: '/api/newservice',
    rewritePrefix: '/api/newservice',
  });
});
```

## 🐛 Bug Reports

When filing a bug report, please include:

1. **Description**: Clear description of the issue
2. **Steps to reproduce**: Detailed steps to reproduce the bug
3. **Expected behavior**: What you expected to happen
4. **Actual behavior**: What actually happened
5. **Environment**: OS, Node.js version, npm version
6. **Logs**: Any relevant error messages or logs

## 💡 Feature Requests

For feature requests, please provide:

1. **Use case**: Why this feature is needed
2. **Description**: Detailed description of the feature
3. **Examples**: Code examples or mockups if applicable
4. **Alternatives**: Any alternative solutions considered

## 📋 Pull Request Process

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow coding standards
   - Add tests for new functionality
   - Update documentation if needed

3. **Test your changes**
   ```bash
   npm run lint
   npm run test
   ```

4. **Commit your changes**
   ```bash
   git commit -m "feat: add amazing new feature"
   ```

5. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request**
   - Use a clear title and description
   - Reference any related issues
   - Include screenshots if applicable

### Pull Request Checklist

- [ ] Code follows project conventions
- [ ] Tests pass locally
- [ ] New tests added for new functionality
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
- [ ] Commit messages follow conventional commits

## 🔍 Code Review Process

1. All pull requests require review from maintainers
2. Automated checks must pass (CI/CD pipeline)
3. Address any feedback from reviewers
4. Once approved, maintainers will merge the PR

## 📚 Resources

- [Fastify Documentation](https://fastify.io/docs/)
- [Node.js Documentation](https://nodejs.org/docs/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)

## 💬 Getting Help

- **Issues**: [GitHub Issues](https://github.com/yourusername/microservice-gateway/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/microservice-gateway/discussions)

## 📜 Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- Be respectful and inclusive
- Welcome newcomers and help them get started
- Focus on constructive feedback
- Respect different viewpoints and experiences

Thank you for contributing! 🎉
