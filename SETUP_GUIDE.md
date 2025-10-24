# QuizYourFaith - 3 User Levels Implementation Guide

## Overview

This implementation adds comprehensive multiplayer functionality and 3-tier user management system to QuizYourFaith:

### User Levels:
1. **Super Admin** - Full system control and user management
2. **Normal Admin** - Game moderation and community management  
3. **User (Player)** - Regular gameplay with multiplayer features

## Features Implemented

### Database Schema
- ✅ 3 user roles: `super_admin`, `admin`, `user`
- ✅ Multiplayer tables: friends, matches, match_players, invitations
- ✅ Tournament system with participants
- ✅ Chat system with moderation
- ✅ Match reporting system
- ✅ Achievement system for multiplayer

### Backend Controllers
- ✅ Enhanced User model with role-based methods
- ✅ Comprehensive authentication middleware
- ✅ SuperAdminController with full system management
- ✅ GameAdminController for moderation
- ✅ MultiplayerController for real-time game management
- ✅ FriendsController for social features
- ✅ API controller with JWT authentication
- ✅ WebSocket server for real-time features

### Frontend Views
- ✅ Super admin dashboard with analytics
- ✅ Game admin dashboard for moderation
- ✅ Enhanced user dashboard with multiplayer stats
- ✅ Multiplayer lobby with match browsing
- ✅ Friends management interface
- ✅ Real-time chat integration

### Real-time Features
- ✅ WebSocket server with Socket.io
- ✅ Real-time match updates
- ✅ Live chat system
- ✅ Friend status updates
- ✅ Match invitations

## Installation Steps

### 1. Database Setup

```sql
-- Run the multiplayer schema
mysql -u root -p qyf_db < sql/multiplayer_schema.sql

-- Update existing users to have proper roles
UPDATE users SET role = 'user' WHERE role = 'admin';
UPDATE users SET role = 'super_admin' WHERE email = 'admin@example.com';
```

### 2. Install Dependencies

```bash
# PHP dependencies (add to composer.json)
composer require firebase/php-jwt

# WebSocket server dependencies
cd websocket
npm install
```

### 3. Environment Configuration

Create `.env` file in the root directory:
```env
# Database
DB_HOST=localhost
DB_NAME=qyf_db
DB_USER=root
DB_PASSWORD=your_password

# JWT Secret
JWT_SECRET=your-super-secret-jwt-key-change-this

# WebSocket
WS_PORT=3001
FRONTEND_URL=http://localhost

# Email (optional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
```

### 4. WebSocket Server Setup

```bash
cd websocket
npm install

# Start the WebSocket server
npm start

# Or for development with auto-restart
npm run dev
```

### 5. Update Configuration Files

Update `config/config.php`:
```php
<?php
return [
    'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
    'DB_NAME' => $_ENV['DB_NAME'] ?? 'qyf_db',
    'DB_USER' => $_ENV['DB_USER'] ?? 'root',
    'DB_PASS' => $_ENV['DB_PASS'] ?? '',
    'JWT_SECRET' => $_ENV['JWT_SECRET'] ?? 'default-secret-change-this',
    'WS_PORT' => $_ENV['WS_PORT'] ?? 3001,
    'FRONTEND_URL' => $_ENV['FRONTEND_URL'] ?? 'http://localhost',
];
?>
```

### 6. File Permissions

```bash
# Ensure proper permissions
chmod 755 -R app/
chmod 755 -R websocket/
chmod 644 app/config/*.php
```

## Usage Guide

### Super Admin Features

1. **Access**: Login with super_admin role → `/admin/super`
2. **User Management**: View, edit, ban/unban users
3. **System Settings**: Configure multiplayer settings
4. **Analytics**: View system statistics and reports
5. **Tournament Management**: Create and manage tournaments
6. **Server Monitoring**: Monitor multiplayer servers

### Normal Admin Features

1. **Access**: Login with admin role → `/admin/game`
2. **Match Moderation**: Monitor active matches, kick players
3. **Report Handling**: Review and resolve player reports
4. **Chat Moderation**: Moderate chat messages
5. **Tournament Oversight**: Manage tournament events
6. **Announcements**: Send system announcements

### User Features

1. **Multiplayer Gaming**: Join/create matches, real-time gameplay
2. **Friend System**: Add friends, send invitations
3. **Tournaments**: Join tournaments, compete for prizes
4. **Achievements**: Earn multiplayer achievements
5. **Statistics**: View detailed game statistics
6. **Social Features**: Chat with friends, invite to matches

## API Endpoints

### Authentication
- `POST /api/token` - Get JWT token
- `POST /api/user/online-status` - Update online status

### Multiplayer
- `GET /api/match/{id}/players` - Get match players
- `GET /api/friends/online` - Get online friends
- `GET /api/match/invitations` - Get match invitations
- `POST /api/match/invitation/{id}/accept` - Accept invitation

### Tournaments
- `GET /api/tournaments` - List tournaments
- `POST /api/tournament/{id}/join` - Join tournament

### Reports
- `POST /api/report/player` - Report a player

## WebSocket Events

### Client → Server
- `join_match` - Join a match room
- `leave_match` - Leave a match room
- `send_message` - Send chat message
- `submit_answer` - Submit quiz answer
- `update_status` - Update online status
- `invite_friend` - Invite friend to match

### Server → Client
- `match_state` - Current match state
- `new_message` - New chat message
- `answer_result` - Answer submission result
- `players_update` - Player scores update
- `match_completed` - Match finished
- `friend_status_update` - Friend status changed
- `match_invitation` - Received match invitation

## Security Features

### Authentication & Authorization
- JWT-based API authentication
- Role-based access control
- CSRF protection on forms
- Rate limiting on API endpoints

### Anti-Cheat Measures
- Answer validation server-side
- Score calculation verification
- Match result validation
- Player behavior monitoring

### Chat Moderation
- Profanity filtering
- Message rate limiting
- Admin message deletion
- User reporting system

## Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**
   - Check if WebSocket server is running
   - Verify firewall settings
   - Check browser console for errors

2. **Database Connection Errors**
   - Verify database credentials in `.env`
   - Ensure database exists and is accessible
   - Check if all tables were created

3. **JWT Token Issues**
   - Verify JWT_SECRET is set and secure
   - Check token expiration settings
   - Ensure proper token validation

4. **Permission Errors**
   - Check file permissions
   - Verify .htaccess configuration
   - Ensure proper role assignments

### Logs and Debugging

- Check `storage/logs/` for application logs
- WebSocket server logs to console
- Browser developer console for client-side errors
- Database query logs for SQL issues

## Performance Optimization

### Database Optimization
- Add appropriate indexes on frequently queried columns
- Use prepared statements for all queries
- Implement query result caching

### WebSocket Optimization
- Implement connection pooling
- Use Redis for scaling multiple server instances
- Optimize message broadcasting

### Frontend Optimization
- Implement lazy loading for large datasets
- Use pagination for friend lists and match history
- Cache static assets with proper headers

## Scaling Considerations

### Horizontal Scaling
- Use Redis for WebSocket session storage
- Implement load balancing for WebSocket servers
- Use database read replicas

### Vertical Scaling
- Optimize database queries
- Implement caching layers
- Use CDN for static assets

## Maintenance

### Regular Tasks
- Monitor system logs for errors
- Review and moderate user reports
- Update banned user lists
- Backup database regularly

### Updates
- Keep dependencies updated
- Monitor security advisories
- Test updates in staging environment
- Maintain backward compatibility

## Support

For issues and questions:
- Check the troubleshooting section
- Review application logs
- Consult the WebSocket server logs
- Verify database integrity

This implementation provides a solid foundation for a multiplayer quiz platform with comprehensive user management and real-time features.