# WPShadow Test Environment

## Quick Start

```bash
./test-env.sh start
```

Then open: 

**http://stunning-fishstick-j69p5j559jqcpw79-8888.app.github.dev/**

## Commands

```bash
./test-env.sh start    # Start WordPress test environment
./test-env.sh stop     # Stop the environment
./test-env.sh restart  # Restart everything
./test-env.sh clean    # Remove all data (fresh install)
./test-env.sh logs     # View logs
```

## What This Does

- Spins up WordPress + MySQL in Docker containers
- Mounts your plugin at `/wp-content/plugins/wpshadow`
- Runs on HTTP port 8888
- Takes ~30 seconds to start
- Fresh WordPress install each time you run `clean`

## Testing Your Plugin

1. Start environment: `./test-env.sh start`
2. Complete WordPress installation via browser
3. Activate your plugin from Plugins page
4. Test functionality
5. Make code changes (they're live-mounted)
6. Refresh browser to see changes

## Notes

- Plugin files are live-mounted, changes appear immediately
- Database persists between `start`/`stop` (use `clean` to reset)
- No complex SSL configuration
- No hours of setup
- Just works™
