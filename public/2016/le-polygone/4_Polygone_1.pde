/*
* ----------------------------------------------------------------------------
* "THE BEER-WARE LICENSE" (Revision 42):
* <n.peugnet@free.fr> wrote this file. As long as you retain this notice you
* can do whatever you want with this stuff. If we meet some day, and you think
* this stuff is worth it, you can buy me a beer in return. Poul-Henning Kamp
* ----------------------------------------------------------------------------
*/

int nbPoints = 15;
ArrayList<Point> pts;
int tempMillis = 0;
int longueurTraits;

void setup() 
{
  size(900, 600);
  longueurTraits = int((width+height)/3.4);
  print(longueurTraits);
  pts = new ArrayList<Point>();
  for (int i=0; i< nbPoints; i++)
  {
    pts.add(new Point());
  }
}

void draw() 
{
  int removeNb = 0;
  if(millis() - tempMillis > 17)
  {
    background(255,255,255);
    tempMillis = millis();
    fill (0);
    stroke(0);
    for (int i=0; i<pts.size(); i++) 
    {
      Point pt = pts.get(i);
      if (i >= nbPoints) 
      {
        if (pt.getDuree()< millis())
        {
          removeNb = i;
        }
      }
      pt.move();
      pt.drawPoint();
    }
    
    if(removeNb >= nbPoints) 
    {
      pts.remove(removeNb);
      removeNb = 0;
    }
    
    for (int i=0; i<pts.size() - 1; i++)
    {
      Point pt1 = pts.get(i);
      for (int j=i+1; j < pts.size(); j++)
      {
        Point pt2 = pts.get(j);
        float distance = pt1.distance(pt2);
        if (distance < longueurTraits)
        {
          pt1.drawLine(pt2, int((1-distance/longueurTraits)*100));
        }
      }
    }
  }
}

void mousePressed() {
  pts.add(new Point(mouseX, mouseY, 20000));
}

class Point 
{

  float x;
  float y;
  float endX;
  float endY;
  float distX;
  float distY;
  float step = 0.005;
  float avancee = 1;
  int duree;
  
  Point()
  {
    x = random(0, width);
    y = random(0, height);
    duree = 0;
  }
  
  Point(int inX, int inY, int inDuree)
  {
    x = inX;
    y = inY;
    duree = millis() + inDuree;
  }
  
  void setDestination()
  {
    endX = random(0, width);
    endY = random(0, height);
    distX = endX - x;
    distY = endY - y;
    avancee = 0;
    setStep();
  }
  
  void setStep() 
  {
    step = random(0.0001, 0.005);
  }
  
  void move() 
  {
    if (avancee >= 1) 
    {
      setDestination();
    }
    x += distX * step;
    y += distY * step;
    avancee += step;
  }
  
  float getX()
  {
    return x;
  }
  
  float getY() 
  {
    return y;
  }
  
  int getDuree()
  {
    return duree;
  }
  
  void drawPoint()
  {
    ellipse(x, y, 2, 2);
  }
  
  void drawLine(Point pt)
  {
    line(x, y, pt.getX(), pt.getY());
  }
  
  void drawLine(Point pt, int opacite)
  {
    stroke(0, opacite);
    line(x, y, pt.getX(), pt.getY());
  }

  float distance(Point pt2)
  {
    return dist(x, y, pt2.getX(), pt2.getY());
  }
  
}


