#!/bin/bash
# WPShadow Test Environment - Quick Start/Stop Script

cd "$(dirname "$0")"

case "${1}" in
  start)
    echo "🚀 Starting WordPress test environment..."
    # Start Docker if not running
    if ! docker ps &>/dev/null; then
      echo "Starting Docker daemon..."
      sudo dockerd > /tmp/dockerd.log 2>&1 &
      sleep 3
    fi
    docker-compose -f docker-compose-test.yml up -d
    echo ""
    echo "✅ WordPress test environment is starting..."
    echo "⏳ Waiting 10 seconds for services..."
    sleep 10
    echo ""
    echo "🌐 Access your WordPress installation at:"
    echo ""
    echo "   http://stunning-fishstick-j69p5j559jqcpw79-8888.app.github.dev/"
    echo ""
    echo "📦 Your plugin is at: /wp-content/plugins/wpshadow"
    echo ""
    ;;
  
  stop)
    echo "🛑 Stopping WordPress test environment..."
    docker-compose -f docker-compose-test.yml down
    echo "✅ Stopped"
    ;;
  
  restart)
    echo "🔄 Restarting..."
    $0 stop
    sleep 2
    $0 start
    ;;
  
  clean)
    echo "🧹 Cleaning all data (fresh install)..."
    docker-compose -f docker-compose-test.yml down -v
    echo "✅ All data removed"
    ;;
  
  logs)
    docker-compose -f docker-compose-test.yml logs -f
    ;;
  
  *)
    echo "WPShadow Test Environment"
    echo ""
    echo "Usage: $0 {start|stop|restart|clean|logs}"
    echo ""
    echo "Commands:"
    echo "  start    - Start WordPress (port 8888)"
    echo "  stop     - Stop WordPress"
    echo "  restart  - Restart WordPress"
    echo "  clean    - Remove all data and start fresh"
    echo "  logs     - View container logs"
    exit 1
    ;;
esac
